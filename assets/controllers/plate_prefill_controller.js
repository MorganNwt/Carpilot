import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["plate", "btn", "error"];
  static values = {
    endpoint: String,  // "/mock/plate-lookup"
    redirect: String,  // URL page formulaire pré-rempli
    login: String,     // "/account"
  };

  connect() {
    // au chargement, on nettoie si une valeur existe déjà
    console.log("plate-prefill connecté");
    if (this.hasPlateTarget) {
      const compact = this.compactPlate(this.plateTarget.value);
      if (compact) this.plateTarget.value = this.formatPlate(compact.slice(0, 7));
    }
  }

  // ======================
  // Events (appelés via data-action)
  // ======================

  onInput(event) {
  let value = event.target.value;

  // Nettoyage : lettres + chiffres uniquement
  value = value
    .toUpperCase()
    .replace(/[^A-Z0-9]/g, "");

  // Limite logique : AA123AA (7 chars)
  value = value.slice(0, 7);

  // Construction avec tirets
  let formatted = "";

  if (value.length > 0) formatted += value.slice(0, 2);
  if (value.length > 2) formatted += "-" + value.slice(2, 5);
  if (value.length > 5) formatted += "-" + value.slice(5, 7);

  event.target.value = formatted;
  this.hideError();
}

  onBlur(event) {
    const compact = this.compactPlate(event.target.value);

    if (!compact) return;

    if (this.isValidPlate(compact)) {
      event.target.value = this.formatPlate(compact);
      this.hideError();
    } else {
      this.showError("Plaque invalide. Format attendu : AA-123-AA");
    }
  }

  async submit(event) {
    event.preventDefault();

    const compact = this.compactPlate(this.plateTarget.value);

    if (!compact) {
      this.showError("Veuillez saisir une plaque.");
      return;
    }

    if (!this.isValidPlate(compact)) {
      this.showError("Plaque invalide. Format attendu : AA-123-AA");
      return;
    }

    // Format final sûr
    const plate = this.formatPlate(compact);
    this.plateTarget.value = plate;

    const jwt = localStorage.getItem("token");

    if (!jwt) {
      sessionStorage.setItem("pending_plate", plate);
      sessionStorage.setItem("after_login_redirect", this.redirectValue);
      sessionStorage.setItem("resume_plate_lookup", "1");

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
    } catch {
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

  // ======================
  // Helpers plaque
  // ======================

  compactPlate(v) {
    return (v || "")
      .toUpperCase()
      .trim()
      .replace(/[^A-Z0-9\s-]/g, "")
      .replace(/[\s-]+/g, "");
  }

  isValidPlate(compact) {
    // Nouveau format FR : AA123AA
    return /^[A-Z]{2}\d{3}[A-Z]{2}$/.test(compact);
  }

  formatPlate(compact) {
    const s = (compact || "").toUpperCase().slice(0, 7);

    const a = s.slice(0, 2);
    const b = s.slice(2, 5);
    const c = s.slice(5, 7);

    if (s.length <= 2) return a;
    if (s.length <= 5) return `${a}-${b}`;
    return `${a}-${b}-${c}`;
  }

  // ======================
  // UI helpers
  // ======================

  setLoading(isLoading) {
    if (!this.hasBtnTarget) return;
    this.btnTarget.disabled = isLoading;
    this.btnTarget.classList.toggle("opacity-60", isLoading);
    this.btnTarget.classList.toggle("cursor-not-allowed", isLoading);
  }

  showError(msg) {
    if (!this.hasErrorTarget) return;
    this.errorTarget.textContent = msg;
    this.errorTarget.classList.remove("hidden");
  }

  hideError() {
    if (!this.hasErrorTarget) return;
    this.errorTarget.textContent = "";
    this.errorTarget.classList.add("hidden");
  }
}
