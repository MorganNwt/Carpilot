import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["tbody"];
  static values = {
    listUrl: String, // "/api/agent/estimations"
  };

  connect() {
    const token = localStorage.getItem("token");
    if (!token) {
      window.location.href = "/account";
      return;
    }

    this.currentStatus = "offer_made";
    this.load();
  }

  get token() {
    return localStorage.getItem("token");
  }

  get headers() {
    return {
      Authorization: `Bearer ${this.token}`,
      "Content-Type": "application/json",
      Accept: "application/json",
    };
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
      estimated: ["Estimation...", "bg-blue-100 text-blue-700"],
      offer_made: ["Offre en attente", "bg-orange-100 text-orange-700"],
      in_review: ["En cours d’étude", "bg-yellow-100 text-yellow-800"],
      rejected: ["Refusée", "bg-red-100 text-red-700"],
      transaction_completed: ["Acceptée", "bg-green-100 text-green-700"],
      cancelled: ["Annulée", "bg-gray-200 text-gray-600"],
    };

    const [label, cls] = map[status] || ["—", "bg-gray-100 text-gray-400"];
    return `<span class="px-3 py-1 rounded-full text-xs font-semibold ${cls}">${label}</span>`;
  }

  actions(est) {
    if (est.status === "offer_made") {
      return `
        <button class="px-3 py-1.5 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600"
                data-action="click->agent-estimations#review"
                data-id="${est.id}">
          Prendre en charge
        </button>
      `;
    }

    if (est.status === "in_review") {
      return `
        <button class="px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700"
                data-action="click->agent-estimations#accept"
                data-id="${est.id}">
          Accepter
        </button>
        <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700 ml-2"
                data-action="click->agent-estimations#reject"
                data-id="${est.id}">
          Refuser
        </button>
      `;
    }

    return `<span class="text-gray-400 text-xs">Aucune action</span>`;
  }

  /**
   * Si ton API renvoie des VEHICLES (comme ton exemple), adapte ici :
   * - est = e.estimation
   * - v = e
   * - seller = e.seller / e.sellerFirstName...
   */
    row(e) {
    // Vehicle peut être: e (vehicle dto) OU e.vehicle (wrapper/estimation dto)
    const vehicle = e?.plate ? e : (e?.vehicle ?? {});

    // Estimation peut être:
    // - e.estimation (vehicle dto)
    // - e.estimation (wrapper)
    // - e (si e est déjà une estimation dto)
    // - vehicle.estimation (vehicle dto)
    const est =
        e?.estimated_price != null || e?.status
        ? e
        : (e?.estimation ?? vehicle?.estimation ?? null);

    // Client : peut être sur vehicle (ton VehicleResponseDto)
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
            <div class="font-semibold">${vehicle?.plate ?? "—"}</div>
            <div class="text-gray-500">${(vehicle?.brand ?? "")} ${(vehicle?.model ?? "")}</div>
        </td>

        <td class="p-4">${client}</td>

        <td class="p-4">${this.formatDate(estDate)}</td>

        <td class="p-4">${est?.estimated_price != null ? `${est.estimated_price} €` : "—"}</td>

        <td class="p-4">${est?.offer_price != null ? `${est.offer_price} €` : "—"}</td>

        <td class="p-4">${this.badge(est?.status)}</td>

        <td class="p-4 text-right">
            ${est?.id ? this.actions(est) : `<span class="text-gray-400 text-xs">Aucune estimation</span>`}
        </td>
        </tr>
    `;
    }



  async load() {
    try {
      this.tbodyTarget.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="7">Chargement…</td></tr>`;

      const qs = this.currentStatus ? `?status=${encodeURIComponent(this.currentStatus)}` : "";
      const url = (this.listUrlValue || "/api/agent/estimations") + qs;

      const res = await fetch(url, { headers: this.headers });
      if (!res.ok) {
        const err = await res.json().catch(() => null);
        toastr.error(err?.message ?? "Impossible de charger les estimations");
        return;
      }

      const data = await res.json();

      if (!data.length) {
        this.tbodyTarget.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="7">Aucun dossier</td></tr>`;
        return;
      }

      this.tbodyTarget.innerHTML = data.map((item) => this.row(item)).join("");
    } catch {
      toastr.error("Erreur réseau");
    }
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
    try {
      const res = await fetch(`/api/agent/estimations/${id}/${action}`, {
        method: "POST",
        headers: this.headers,
      });

      if (!res.ok) {
        const err = await res.json().catch(() => null);
        toastr.error(err?.message ?? "Action impossible");
        return;
      }

      toastr.success(successMsg);
      await this.load();
    } catch {
      toastr.error("Erreur réseau");
    }
  }
}
