import { Controller } from "@hotwired/stimulus";

export default class extends Controller {

  connect() {
    // Gestion de l'affichage du message "Récupération de votre véhicule en cours..." 
    // lorsque l'utilisateur est redirigé vers la page de connexion depuis une page nécessitant une authentification.
    const url = new URL(window.location.href);
    const hasRedirectQuery = !!url.searchParams.get("redirect");
    const hasRedirectStorage = !!sessionStorage.getItem("after_login_redirect");

    if (hasRedirectQuery || hasRedirectStorage) {
      this.element.classList.remove("hidden");
    }
  }
}