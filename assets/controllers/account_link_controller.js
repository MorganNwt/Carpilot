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

    if (!token) {
      this.showLoggedOut();
      return;
    }

    this.showLoggedIn();

    if (this.hasDashboardLinkTarget) {
      this.dashboardLinkTarget.href = this.getDashboardUrlFromToken(token);
    }
  }

  go(event) {
    const token = localStorage.getItem("token");

    if (!token) return;

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
    const sellerDash = this.sellerUrlValue || "/seller/dashboard";
    const agentDash = this.agentUrlValue || "/agent/dashboard";
    const adminDash = this.adminUrlValue || "/admin/dashboard";

    const roles = this.getRolesFromJwt(token);

    if (roles.includes("ROLE_ADMIN")) return adminDash;
    if (roles.includes("ROLE_AGENT")) return agentDash;
    if (roles.includes("ROLE_SELLER")) return sellerDash;

    return sellerDash;
  }

  getRolesFromJwt(token) {
    const parts = String(token || "").split(".");
    if (parts.length < 2) return [];

    try {
      const payload = parts[1];
      const json = JSON.parse(this.base64UrlDecode(payload));
      return Array.isArray(json?.roles) ? json.roles : [];
    } catch {
      return [];
    }
  }

  base64UrlDecode(str) {
    let base64 = str.replace(/-/g, "+").replace(/_/g, "/");

    while (base64.length % 4) {
      base64 += "=";
    }

    return decodeURIComponent(
      Array.prototype.map
        .call(atob(base64), (char) => "%" + ("00" + char.charCodeAt(0).toString(16)).slice(-2))
        .join("")
    );
  }
}