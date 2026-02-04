import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["accountLink", "logoutBtn", "dashboardLink"];

  static values = {
    sellerUrl: String,
    agentUrl: String,
    adminUrl: String,
  };

  connect() {
    const token = localStorage.getItem("token");

    // Pas connecté => afficher Mon compte, cacher logout/dashboard
    if (!token) {
      this.showLoggedOut();
      return;
    }

    // Connecté => cacher Mon compte, afficher logout/dashboard
    this.showLoggedIn();

    // Met à jour le lien Dashboard avec la bonne destination
    const dash = this.getDashboardUrlFromToken(token);
    if (this.hasDashboardLinkTarget) {
      this.dashboardLinkTarget.href = dash;
    }
  }

  go(event) {
    const token = localStorage.getItem("token");

    // pas connecté => laisser aller sur /account
    if (!token) return;

    // connecté => dashboard selon rôle
    event.preventDefault();
    window.location.href = this.getDashboardUrlFromToken(token);
  }

  goDashboard(event) {
    event.preventDefault();
    const token = localStorage.getItem("token");
    if (!token) {
      window.location.href = "/account";
      return;
    }
    window.location.href = this.getDashboardUrlFromToken(token);
  }

  showLoggedIn() {
    if (this.hasAccountLinkTarget) this.accountLinkTarget.classList.add("hidden");
    if (this.hasLogoutBtnTarget) this.logoutBtnTarget.classList.remove("hidden");
    if (this.hasDashboardLinkTarget) this.dashboardLinkTarget.classList.remove("hidden");
  }

  showLoggedOut() {
    if (this.hasAccountLinkTarget) this.accountLinkTarget.classList.remove("hidden");
    if (this.hasLogoutBtnTarget) this.logoutBtnTarget.classList.add("hidden");
    if (this.hasDashboardLinkTarget) this.dashboardLinkTarget.classList.add("hidden");
  }

  getDashboardUrlFromToken(token) {
    // fallback
    const sellerDash = this.sellerUrlValue || "/seller/dashboard";
    const agentDash = this.agentUrlValue || "/agent/dashboard";
    const adminDash = this.adminUrlValue || "/admin/dashboard";

    const roles = this.getRolesFromJwt(token);

    if (roles.includes("ROLE_ADMIN")) return adminDash;
    if (roles.includes("ROLE_AGENT")) return agentDash;
    if (roles.includes("ROLE_SELLER")) return sellerDash;

    // fallback si pas de rôle lisible
    return sellerDash;
  }

  getRolesFromJwt(token) {
    // JWT = header.payload.signature
    const parts = String(token).split(".");
    if (parts.length < 2) return [];

    try {
      const payload = parts[1];
      const json = JSON.parse(this.base64UrlDecode(payload));
      const roles = json?.roles;

      return Array.isArray(roles) ? roles : [];
    } catch {
      return [];
    }
  }

  base64UrlDecode(str) {
    // base64url -> base64
    let base64 = str.replace(/-/g, "+").replace(/_/g, "/");
    // padding
    while (base64.length % 4) base64 += "=";

    // decode
    return decodeURIComponent(
      Array.prototype.map
        .call(atob(base64), (c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
        .join("")
    );
  }
}
