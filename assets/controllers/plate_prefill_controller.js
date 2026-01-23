import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["plate", "btn", "error"];
  static values = {
    endpoint: String,  // "/mock/plate-lookup"
    redirect: String,  // URL de la page infos véhicule (formulaire pré-rempli)
    login: String,     // "/account"
  };

  async submit(event) {
    event.preventDefault();

    const plate = this.normalize(this.plateTarget.value);
    if (!plate) {
      this.showError("Veuillez saisir une plaque.");
      return;
    }

    // 1) Vérifie connexion (JWT)
    const jwt = localStorage.getItem("token");

    // Pas connecté : on stocke la plaque + info de retour, puis on va sur login
    if (!jwt) {
      // plaque en attente (pour relancer le lookup après login)
      sessionStorage.setItem("pending_plate", plate);

      // info de retour après login (formulaire)
      sessionStorage.setItem("after_login_redirect", this.redirectValue);

      // flag pour savoir qu'on vient du flux plaque
      sessionStorage.setItem("resume_plate_lookup", "1");

      // pré-remplir minimalement pour éviter un écran vide
      sessionStorage.setItem(
        "plate_prefill",
        JSON.stringify({
          plate,
          found: false,
          vehicle: null,
          requiresAuth: true,
        })
      );

      window.location.href = this.loginValue || "/account";
      return;
    }

    // 2) Connecté : lookup direct + stockage + redirection
    await this.lookupAndRedirect(plate);
  }

  async lookupAndRedirect(plate) {
    this.setLoading(true);
    this.hideError();

    const url = `${this.endpointValue}/${encodeURIComponent(plate)}`;

    try {
      const res = await fetch(url, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });

      const json = await res.json().catch(() => null);
      const found = !!json && json.error === false && !!json.data;

      sessionStorage.setItem(
        "plate_prefill",
        JSON.stringify({
          plate,
          found,
          vehicle: found ? json.data : null,
        })
      );

      window.location.href = this.redirectValue;
    } catch (e) {
      sessionStorage.setItem(
        "plate_prefill",
        JSON.stringify({
          plate,
          found: false,
          vehicle: null,
          networkError: true,
        })
      );

      window.location.href = this.redirectValue;
    } finally {
      this.setLoading(false);
    }
  }

  normalize(v) {
    return (v || "")
      .toUpperCase()
      .trim()
      .replace(/\s+/g, "")
      .replace(/[^A-Z0-9-]/g, "");
  }

  setLoading(isLoading) {
    this.btnTarget.disabled = isLoading;
    this.btnTarget.classList.toggle("opacity-60", isLoading);
    this.btnTarget.classList.toggle("cursor-not-allowed", isLoading);
  }

  showError(msg) {
    this.errorTarget.textContent = msg;
    this.errorTarget.classList.remove("hidden");
  }

  hideError() {
    this.errorTarget.textContent = "";
    this.errorTarget.classList.add("hidden");
  }
}
