import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static values = {
    statsUrl: String,
    agentsUrl: String, // URL de l'API pour récupérer les agents
    sellersUrl: String, // URL de l'API pour récupérer les vendeurs
    vehiclesUrl: String, // URL de l'API pour récupérer les véhicules
  };

  static targets = [
    "section",
    "feedback",
    "pagination",
    "agentsTbody",
    "sellersTbody",
    "vehiclesTbody",
    "countUsers",
    "countAgents",
    "countSellers",
    "countVehicles",
  ];

  async connect() {
    this.pages = { agents: 1, sellers: 1, vehicles: 1 };
    const token = localStorage.getItem("token");

    if (!token) {
      window.location.href = "/account";
      return;
    }

    await this.loadAll();
    this.showSection("sellers");
  }

  async safeJson(url) {
    const token = localStorage.getItem("token");

    if (!token) {
      window.location.href = "/account";
      return null;
    }

    try {
      const res = await fetch(url, {
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
        },
      });

      if (res.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/account";
        return null;
      }

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
      }

      return await res.json();
    } catch (e) {
      this.message("Impossible de charger certaines données. Réessaie en actualisant la page.", true);
      console.error("API error:", url, e);
      return null;
    }
  }

  async loadAll() {
    const [agents, sellers, vehicles, stats] = await Promise.all([
      this.safeJson(`${this.agentsUrlValue}?page=${this.pages.agents}&limit=10`),
      this.safeJson(`${this.sellersUrlValue}?page=${this.pages.sellers}&limit=10`),
      this.safeJson(`${this.vehiclesUrlValue}?page=${this.pages.vehicles}&limit=10`),
      this.safeJson(this.statsUrlValue),
    ]);

    const agentsData = agents?.data ?? agents ?? [];
    const sellersData = sellers?.data ?? sellers ?? [];
    const vehiclesData = Array.isArray(vehicles) ? vehicles : (vehicles?.data ?? []);

    this.renderAgents(this.agentsTbodyTarget, agentsData);
    this.renderSellers(this.sellersTbodyTarget, sellersData);
    this.renderVehicles(this.vehiclesTbodyTarget, vehiclesData);

    this.renderPagination("agents", agents?.meta);
    this.renderPagination("sellers", sellers?.meta);
    this.renderPagination("vehicles", vehicles?.meta);
    const counts = stats?.counts;
    this.countUsersTarget.textContent = String(counts?.users ?? "—");
    this.countAgentsTarget.textContent = String(counts?.agents ?? "—");
    this.countSellersTarget.textContent = String(counts?.sellers ?? "—");
    this.countVehiclesTarget.textContent = String(counts?.vehicles ?? "—");
  }

  show(event) {
    const section = event.currentTarget.dataset.section;
    this.showSection(section);
  }

  showSection(sectionName) {
    this.sectionTargets.forEach((el) => {
      el.classList.toggle("hidden", el.dataset.section !== sectionName);
    });
  }

  renderAgents(tbody, users) {
    if (!Array.isArray(users) || users.length === 0) {
      tbody.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="6">Aucune donnée.</td></tr>`;
      return;
    }

    tbody.innerHTML = users.map((u) => {
      const id = u.id ?? "—";
      const name = [u.firstName, u.lastName].filter(Boolean).join(" ") || "—";
      const email = u.email ?? "—";
      const phone = u.phone ?? "—";
      const employeeId = u.employeeId ?? "—";

      return `
        <tr class="border-t">
          <td class="p-4">${this.escape(id)}</td>
          <td class="p-4">${this.escape(name)}</td>
          <td class="p-4">${this.escape(email)}</td>
          <td class="p-4">${this.escape(phone)}</td>
          <td class="p-4">${this.escape(employeeId)}</td>
          <td class="p-4">${this.deleteButton("agents", id, name)}</td>
        </tr>
      `;
    }).join("");
  }

  renderSellers(tbody, users) {
    if (!Array.isArray(users) || users.length === 0) {
      tbody.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="7">Aucune donnée.</td></tr>`;
      return;
    }

    tbody.innerHTML = users.map((u) => {
      const id = u.id ?? "—";
      const name = [u.firstName, u.lastName].filter(Boolean).join(" ") || "—";
      const email = u.email ?? "—";
      const phone = u.phone ?? "—";
      const address = u.fullAddress ?? "—";
      const vehiclesCount = Array.isArray(u.vehicles) ? u.vehicles.length : 0;

      return `
        <tr class="border-t">
          <td class="p-4">${this.escape(id)}</td>
          <td class="p-4">${this.escape(name)}</td>
          <td class="p-4">${this.escape(email)}</td>
          <td class="p-4">${this.escape(phone)}</td>
          <td class="p-4">${this.escape(address)}</td>
          <td class="p-4">${this.escape(vehiclesCount)}</td>
          <td class="p-4">${this.deleteButton("sellers", id, name)}</td>
        </tr>
      `;
    }).join("");
  }

  renderVehicles(tbody, vehicles) {
    if (!Array.isArray(vehicles) || vehicles.length === 0) {
      tbody.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="6">Aucun véhicule.</td></tr>`;
      return;
    }

    tbody.innerHTML = vehicles.map((v) => {
      const id = v.id ?? "—";
      const brand = v.brand ?? v.make ?? "—";
      const model = v.model ?? "—";
      const registrationDate = v.registrationDate ?? "—";
      const plate = v.licensePlate ?? v.plate ?? "—";
      const vin = v.vin ?? "—";

      return `
        <tr class="border-t">
          <td class="p-4">${this.escape(id)}</td>
          <td class="p-4">${this.escape(`${brand} ${model}`.trim())}</td>
          <td class="p-4">${this.escape(registrationDate)}</td>
          <td class="p-4">${this.escape(plate)}</td>
          <td class="p-4">${this.escape(vin)}</td>
          <td class="p-4">${this.deleteButton("vehicles", id, plate)}</td>
        </tr>
      `;
    }).join("");
  }

  renderPagination(kind, meta) {
    const container = this.paginationTargets.find((el) => el.dataset.kind === kind);
    const page = meta?.currentPage ?? 1;
    const total = Math.max(1, meta?.totalPages ?? 1);
    container.innerHTML = `
      <button type="button" class="px-3 py-2 rounded border cursor-pointer disabled:cursor-not-allowed disabled:opacity-40" data-action="admin-dashboard#changePage"
        data-kind="${kind}" data-page="${page - 1}" ${page <= 1 ? "disabled" : ""}>Précédent</button>
      <span>Page ${page} / ${total}</span>
      <button type="button" class="px-3 py-2 rounded border cursor-pointer disabled:cursor-not-allowed disabled:opacity-40" data-action="admin-dashboard#changePage"
        data-kind="${kind}" data-page="${page + 1}" ${page >= total ? "disabled" : ""}>Suivant</button>`;
  }

  async changePage(event) {
    const button = event.currentTarget;
    button.disabled = true;
    this.pages[button.dataset.kind] = Number(button.dataset.page);
    await this.loadAll();
    window.scrollTo({ top: 0, left: 0, behavior: "instant" });
  }

  message(text, error = false) {
    this.feedbackTarget.textContent = text;
    this.feedbackTarget.className = `mb-4 rounded-lg p-3 ${error ? "bg-red-100 text-red-800" : "bg-green-100 text-green-800"}`;
  }

  deleteButton(kind, id, label) {
    return `<button type="button" class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"
      data-action="click->admin-dashboard#deleteRecord" data-kind="${kind}" data-id="${this.escape(id)}"
      data-label="${this.escape(label)}" aria-label="Supprimer ${this.escape(label)}">Supprimer</button>`;
  }

  async deleteRecord(event) {
    const button = event.currentTarget;
    if (button.disabled) return;
    const { kind, id, label } = button.dataset;
    const urls = { agents: this.agentsUrlValue, sellers: this.sellersUrlValue, vehicles: this.vehiclesUrlValue };
    if (!urls[kind] || !/^\d+$/.test(id)) return;
    const question = kind === "vehicles"
      ? `Supprimer définitivement le véhicule ${label} et son estimation ?`
      : `Supprimer le compte de ${label} ? Son accès sera bloqué et ses dossiers conservés.`;
    if (!window.confirm(question)) return;

    const token = localStorage.getItem("token");
    if (!token) {
      window.location.href = "/account";
      return;
    }
    button.disabled = true;
    try {
      const response = await fetch(`${urls[kind].replace(/\/$/, "")}/${id}`, {
        method: "DELETE",
        headers: { Accept: "application/json", Authorization: `Bearer ${token}` },
      });
      if (response.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/account";
        return;
      }
      if (!response.ok) {
        const body = await response.json().catch(() => null);
        throw new Error(body?.message ?? (response.status === 403 ? "Accès réservé aux administrateurs." : "La suppression a échoué."));
      }
      if (button.closest("tbody").rows.length === 1) {
        this.pages[kind] = Math.max(1, this.pages[kind] - 1);
      }
      this.message(kind === "vehicles" ? "Véhicule supprimé." : "Compte supprimé. Son accès est désormais bloqué.");
      await this.loadAll();
    } catch (error) {
      this.message(error.message || "La suppression a échoué. Réessaie.", true);
    } finally {
      button.disabled = false;
    }
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