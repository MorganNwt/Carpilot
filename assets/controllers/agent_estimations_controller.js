import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["tbody"];

  static values = {
    token: String,   // JWT token pour l'authentification API
    listUrl: String, // ex: "/api/agent/estimations"
  };

  async connect() {
    
    // Si pas de token, rediriger vers la page de connexion
    if (!this.tokenValue) {
      window.location.href = "/account";
      return;
    }

    // Par défaut, on affiche les offres en attente de prise en charge
    this.currentStatus = "offer_made";
    await this.load();
  }

  // En-têtes d'authentification pour les requêtes API
  get headers() {
    return {
      Accept: "application/json",
      "Content-Type": "application/json",
      Authorization: `Bearer ${this.tokenValue}`,
    };
  }

  // Méthode utilitaire pour faire des requêtes API avec gestion d'erreurs et redirection si non autorisé
  async safeJson(url, options = {}) {
    try {
      const res = await fetch(url, {
        ...options,
        headers: {
          ...(options.headers || {}), // fusionne les en-têtes personnalisés avec les en-têtes d'authentification
          ...this.headers, // ajoute les en-têtes d'authentification
        },
      });

      // Si le token est invalide ou expiré, rediriger vers la page de connexion
      if (res.status === 401) {
        window.location.href = "/account";
        return null;
      }

      if (!res.ok) {
        // on tente de lire le message JSON
        const err = await res.json().catch(() => null);
        throw new Error(err?.message ?? `HTTP ${res.status}`);
      }

      return await res.json();
    } catch (e) {
      console.error("API error:", url, e);
      toastr?.error?.(e.message ?? "Erreur API");
      return null;
    }
  }

  formatDate(dateString) {
    if (!dateString) return "—";
    const d = new Date(dateString);
    return d.toLocaleDateString("fr-FR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  async filter(event) {
    this.currentStatus = event.currentTarget.dataset.status || "";
    await this.load();
  }

  badge(status) {
    const map = {
      estimated: ["Offre en attente", "bg-orange-100 text-orange-700"],
      offer_made: ["À traiter", "bg-orange-100 text-orange-700"],
      in_review: ["En cours d’étude", "bg-orange-100 text-orange-700"],
      rejected: ["Refusée", "bg-red-100 text-red-700"],
      transaction_completed: ["Acceptée", "bg-green-100 text-green-700"],
      cancelled: ["Annulée", "bg-red-200 text-red-700"],
    };

    const [label, cls] = map[status] || ["—", "bg-gray-100 text-gray-400"];
    return `<span class="px-3 py-1 rounded-full text-xs font-semibold ${cls}">${label}</span>`;
  }

  actions(est) {
    if (est.status === "offer_made") {
      return `
        <button class="px-3 py-1.5 rounded-lg bg-orange-500 text-white hover:bg-orange-600 cursor-pointer transition"
                data-action="click->agent-estimations#review"
                data-id="${est.id}">
          Prendre en charge
        </button>
      `;
    }

    if (est.status === "in_review") {
      return `
        <button class="px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 cursor-pointer transition"
                data-action="click->agent-estimations#accept"
                data-id="${est.id}">
          Accepter
        </button>
        <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 ml-2 cursor-pointer transition"
                data-action="click->agent-estimations#reject"
                data-id="${est.id}">
          Refuser
        </button>
      `;
    }

    return `<span class="text-gray-400 text-xs">Aucune action</span>`;
  }

  row(e) {
    const vehicle = e?.plate ? e : (e?.vehicle ?? {});
    const est =
      e?.estimated_price != null || e?.status
        ? e
        : (e?.estimation ?? vehicle?.estimation ?? null);

    const firstName = vehicle?.sellerFirstName ?? e?.sellerFirstName ?? "";
    const lastName = vehicle?.sellerLastName ?? e?.sellerLastName ?? "";
    const sellerId = vehicle?.sellerId ?? e?.sellerId ?? null;

    const client =
      (firstName || lastName)
        ? `${firstName} ${lastName}`.trim()
        : (sellerId ? `Client #${sellerId}` : "—");

    const estDate = est?.createdAt ?? null;

    return `
      <tr class="border-t">
        <td class="p-4">
          <div class="font-semibold">${this.escape(vehicle?.plate ?? "—")}</div>
          <div class="text-gray-500">${this.escape(`${vehicle?.brand ?? ""} ${vehicle?.model ?? ""}`.trim())}</div>
        </td>

        <td class="p-4">${this.escape(client)}</td>

        <td class="p-4">${this.escape(this.formatDate(estDate))}</td>

        <td class="p-4">${est?.estimated_price != null ? this.escape(`${est.estimated_price} €`) : "—"}</td>

        <td class="p-4">${est?.offer_price != null ? this.escape(`${est.offer_price} €`) : "—"}</td>

        <td class="p-4">${this.badge(est?.status)}</td>

        <td class="p-4 text-right">
          ${est?.id ? this.actions(est) : `<span class="text-gray-400 text-xs">Aucune estimation</span>`}
        </td>
      </tr>
    `;
  }

  async load() {
    this.tbodyTarget.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="7">Chargement…</td></tr>`;

    const qs = this.currentStatus ? `?status=${encodeURIComponent(this.currentStatus)}` : "";
    const url = (this.listUrlValue || "/api/agent/estimations") + qs;

    const data = await this.safeJson(url);

    const rows = Array.isArray(data) ? data : (data?.data ?? []);
    if (!rows.length) {
      this.tbodyTarget.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="7">Aucun dossier</td></tr>`;
      return;
    }

    this.tbodyTarget.innerHTML = rows.map((item) => this.row(item)).join("");
  }

  async review(event) {
    await this.postAction(event.currentTarget.dataset.id, "review", "Dossier pris en charge");
  }

  async accept(event) {
    await this.postAction(event.currentTarget.dataset.id, "accept", "Dossier accepté");
  }

  async reject(event) {
    await this.postAction(event.currentTarget.dataset.id, "reject", "Dossier refusé");
  }

  async postAction(id, action, successMsg) {
    const url = `/api/agent/estimations/${id}/${action}`;

    const res = await this.safeJson(url, {
      method: "POST",
      body: JSON.stringify({}),
    });

    if (!res) return;

    toastr?.success?.(successMsg);
    await this.load();
  }

  escape(value) {
    return String(value)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
}