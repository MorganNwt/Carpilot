import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  logout() {
    try {
      localStorage.removeItem("token");
      sessionStorage.clear();
    } catch (e) {
      console.warn("Erreur nettoyage storage", e);
    }
  }
}