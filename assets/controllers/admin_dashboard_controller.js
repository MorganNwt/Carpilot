import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static values = {
    token: String,  // token JWT injecté depuis Twig
    agentsUrl: String, // URL API pour récupérer les agents
    sellersUrl: String, // URL API pour récupérer les sellers
    vehiclesUrl: String, // URL API pour récupérer les véhicules
  };

  // Targets pour les sections et les tableaux
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

  // à la connexion, vérifie le token et charge les données
  async connect() {
    // Si pas de jwt injecté => on redirige vers la page de login
    if (!this.tokenValue) {
      window.location.href = "/account";
      return;
    }

    // Charge les données et affiche la section sellers par défaut
    await this.loadAll();
    this.showSection("sellers");
  }

  // Méthode utilitaire pour faire des requêtes API avec gestion d'erreur et redirection si token invalide
  async safeJson(url) {
    try {
      // Fait une requête fetch avec le token dans les headers
      const res = await fetch(url, {
        // Ajoute les headers d'authentification
        headers: {
          // Indique que l'on attend du JSON en réponse
          Accept: "application/json",
          Authorization: `Bearer ${this.tokenValue}`, // token twig
        },
      });

      // Si le token est invalide ou expiré, redirige vers la page de login
      if (res.status === 401) {
        window.location.href = "/account";
        return null;
      }

      // Si la réponse n'est pas ok, lance une erreur pour être catchée
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } catch (e) {
      console.error("API error:", url, e);
      return null;
    }
  }

  // Charge les données des agents, sellers et véhicules, puis met à jour les tableaux et les compteurs
  async loadAll() {
    // Fait les 3 requêtes en parallèle pour gagner du temps
    const [agents, sellers, vehicles] = await Promise.all([
      this.safeJson(this.agentsUrlValue),
      this.safeJson(this.sellersUrlValue),
      this.safeJson(this.vehiclesUrlValue),
    ]);

    // Normalise les données pour s'assurer d'avoir des tableaux même en cas de réponse inattendue
    const agentsData = agents?.data ?? agents ?? [];
    const sellersData = sellers?.data ?? sellers ?? [];
    const vehiclesData = Array.isArray(vehicles) ? vehicles : (vehicles?.data ?? []);

    // Met à jour les tableaux et les compteurs avec les données récupérées
    this.renderAgents(this.agentsTbodyTarget, agentsData);
    this.renderSellers(this.sellersTbodyTarget, sellersData);
    this.renderVehicles(this.vehiclesTbodyTarget, vehiclesData);

    // Calcule le nombre total d'utilisateurs (agents + sellers) pour le compteur global
    const usersCount = (agentsData?.length ?? 0) + (sellersData?.length ?? 0);

    // Met à jour les compteurs dans l'interface
    this.countUsersTarget.textContent = String(usersCount);
    this.countAgentsTarget.textContent = String(agentsData?.length ?? 0);
    this.countSellersTarget.textContent = String(sellersData?.length ?? 0);
    this.countVehiclesTarget.textContent = String(vehiclesData?.length ?? 0);
  }

  // Affiche la section correspondante au clic sur les boutons de filtre
  show(event) {
    const section = event.currentTarget.dataset.section;
    this.showSection(section);
  }

  // Affiche la section demandée et cache les autres
  showSection(sectionName) {
    this.sectionTargets.forEach((el) => {
      el.classList.toggle("hidden", el.dataset.section !== sectionName);
    });
  }

  // Méthodes de rendu pour les tableaux, avec gestion des cas où les données sont manquantes ou vides
  renderAgents(tbody, users) {
    if (!Array.isArray(users) || users.length === 0) {
      tbody.innerHTML = `<tr><td class="p-4 text-gray-400" colspan="5">Aucune donnée.</td></tr>`;
      return;
    }

    // Pour chaque utilisateur, crée une ligne de tableau en échappant les données pour éviter les problèmes de sécurité
    tbody.innerHTML = users.map((u) => {
      const id = u.id ?? "—";
      const name = [u.firstName, u.lastName].filter(Boolean).join(" ") || "—";
      const email = u.email ?? "—";
      const phone = u.phone ?? "—";
      const employeeId = u.employeeId ?? "—";

      // Retourne une ligne de tableau avec les données de l'utilisateur, en échappant les valeurs pour éviter les injections
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
        </tr>
      `;
    }).join("");
  }

  // Méthode d'échappement pour éviter les problèmes de sécurité liés à l'injection de données dans le HTML
  escape(value) {
    return String(value)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
}