import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["registerForm", "registerError"];

  async register(event) {
    event.preventDefault();
    this.registerErrorTarget.textContent = "";

    const form = this.registerFormTarget;

    const password = form.querySelector('[name="password"]').value;
    const passwordConfirm = form.querySelector('[name="passwordConfirm"]').value;

    if (password !== passwordConfirm) {
      this.registerErrorTarget.textContent = "Les mots de passe ne correspondent pas";
      return;
    }

    const specialsCount = (password.match(/[^a-zA-Z0-9]/g) || []).length;

    if (
      password.length < 13 ||
      !/[A-Z]/.test(password) ||
      !/[0-9]/.test(password) ||
      specialsCount < 2
    ) {
      this.registerErrorTarget.textContent =
        "Mot de passe invalide : 13 caractères, 1 majuscule, 1 chiffre et 2 caractères spéciaux requis.";
      return;
    }

    const rgpdConsent = form.querySelector('[name="rgpdConsent"]').checked;
    if (!rgpdConsent) {
      this.registerErrorTarget.textContent =
        "Vous devez accepter la politique de confidentialité (RGPD).";
      return;
    }

    try {
      const response = await fetch("/api/sellers", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          lastName: form.lastName.value,
          firstName: form.firstName.value,
          email: form.email.value,
          password,
          phone: form.phone.value,
          address: form.address.value,
          city: form.city.value,
          postalCode: form.postalCode.value,
          country: "France",
          rgpdConsent,
        }),
      });

      if (!response.ok) throw new Error("Erreur lors de la création du compte");

      alert("Compte créé avec succès. Vous pouvez vous connecter.");
      form.reset();
    } catch (error) {
      this.registerErrorTarget.textContent = error.message;
    }
  }
}