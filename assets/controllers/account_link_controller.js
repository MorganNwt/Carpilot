import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static values = {
    dashboardUrl: String,
  };

  go(event) {
    const token = localStorage.getItem("token");

    // si connecté go dashboard !
    if (token) {
      event.preventDefault();
      window.location.href = this.dashboardUrlValue || "/seller/dashboard";
    }
    // sinon rester sur /account (login)
  }
}
