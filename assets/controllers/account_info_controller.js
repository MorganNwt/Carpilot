import { Controller } from "@hotwired/stimulus";

export default class extends Controller {

  connect() {
    // Au chargement du composant, on vérifie si on doit afficher le formulaire (si redirection après login ou query param ?)
    const url = new URL(window.location.href);
    const hasRedirectQuery = !!url.searchParams.get("redirect");
    const hasRedirectStorage = !!sessionStorage.getItem("after_login_redirect");

    if (hasRedirectQuery || hasRedirectStorage) {
      this.element.classList.remove("hidden");-
    }
  }
}