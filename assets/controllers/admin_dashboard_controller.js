import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static values = {
    token: String,          // <-- vient du Twig
    agentsUrl: String,
    sellersUrl: String,
    vehiclesUrl: String,
  };

  static targets = [
    "section",
    "agentsTbody",
    "sellersTbody",
    "vehiclesTbody",
    "countUsers",
    "countAgents",
    "countSellers",
    "countVehicles",
  ];

  async connect() {
    // ✅ si pas de jwt injecté => pas normal => redirige
    if (!this.tokenValue) {
      window.location.href = "/account";
      return;
    }

    await this.loadAll();
    this.showSection("agents");
  }

  async safeJson(url) {
    try {
      const res = await fetch(url, {
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${this.tokenValue}`, // ✅ token twig
        },
      });

      if (res.status === 401) {
        window.location.href = "/account";
        return null;
      }

      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } catch (e) {
      console.error("API error:", url, e);
      return null;
    }
  }

  async loadAll() {
    const [agents, sellers, vehicles] = await Promise.all([
      this.safeJson(this.agentsUrlValue),
      this.safeJson(this.sellersUrlValue),
      this.safeJson(this.vehiclesUrlValue),
    ]);

    const agentsData = agents?.data ?? agents ?? [];
    const sellersData = sellers?.data ?? sellers ?? [];
    const vehiclesData = Array.isArray(vehicles) ? vehicles : (vehicles?.data ?? []);

    this.renderAgents(this.agentsTbodyTarget, agentsData);
    this.renderSellers(this.sellersTbodyTarget, sellersData);
    this.renderVehicles(this.vehiclesTbodyTarget, vehiclesData);

    const usersCount = (agentsData?.length ?? 0) + (sellersData?.length ?? 0);

    this.countUsersTarget.textContent = String(usersCount);
    this.countAgentsTarget.textContent = String(agentsData?.length ?? 0);
    this.countSellersTarget.textContent = String(sellersData?.length ?? 0);
    this.countVehiclesTarget.textContent = String(vehiclesData?.length ?? 0);
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
      tbody.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="5">Aucune donnée.</td></tr>`;
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
        </tr>
      `;
    }).join("");
  }

  renderSellers(tbody, users) {
    if (!Array.isArray(users) || users.length === 0) {
      tbody.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="6">Aucune donnée.</td></tr>`;
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
      const year = v.year ?? v.modelYear ?? "—";
      const plate = v.licensePlate ?? v.plate ?? "—";
      const vin = v.vin ?? "—";
      const price = v.price !== null && v.price !== undefined ? `${v.price} €` : "—";

      return `
        <tr class="border-t">
          <td class="p-4">${this.escape(id)}</td>
          <td class="p-4">${this.escape(`${brand} ${model}`.trim())}</td>
          <td class="p-4">${this.escape(year)}</td>
          <td class="p-4">${this.escape(plate)}</td>
          <td class="p-4">${this.escape(vin)}</td>
          <td class="p-4">${this.escape(price)}</td>
        </tr>
      `;
    }).join("");
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