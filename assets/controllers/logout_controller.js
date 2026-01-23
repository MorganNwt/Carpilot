import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    const token = localStorage.getItem("token");
    if (!token) {
      this.element.remove();
    }
  }

  logout() {
    localStorage.removeItem("token");
    sessionStorage.clear();
    window.location.href = "/account";
  }
}