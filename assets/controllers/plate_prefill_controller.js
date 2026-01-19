import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["plate", "btn", "error"];
  static values = {
    endpoint: String, // "/mock/plate-lookup"
    redirect: String, // URL formulaire
  };

  async submit(event) {
    event.preventDefault();

    const plate = this.normalize(this.plateTarget.value);
    if (!plate) {
      this.showError("Veuillez saisir une plaque.");
      return;
    }

    this.setLoading(true);
    this.hideError();

    const url = `${this.endpointValue}/${encodeURIComponent(plate)}`;

    try {
      const res = await fetch(url, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });

      const json = await res.json().catch(() => null);

      // Ton format: { error: false, data: {...} } ou { error: true, data: null }
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
      // Même si panne réseau → on redirige avec formulaire vide
      sessionStorage.setItem(
        "plate_prefill",
        JSON.stringify({ plate, found: false, vehicle: null, networkError: true })
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
