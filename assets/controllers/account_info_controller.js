import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    // Le flag n’existe QUE si on vient du flux plaque
    const redirect = sessionStorage.getItem("after_login_redirect");

    if (redirect) {
      this.element.classList.remove("hidden");
    }
  }
}