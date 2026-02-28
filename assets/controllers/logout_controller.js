import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  logout(event) {
    // laisse la navigation vers /logout se faire
    // mais on nettoie le JWT pour l’API
    localStorage.removeItem("token");
    sessionStorage.clear();
    // pas de window.location ici -> le lien <a href="/logout"> fait le job
  }
}