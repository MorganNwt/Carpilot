import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["profile", "profileTemplate"];

  connect() {
    const token = localStorage.getItem("token");
    if (!token) {
      window.location.href = "/account";
      return;
    }

    this.loadProfile();
  }

  get token() {
    return localStorage.getItem("token");
  }

  get headers() {
    return {
      Authorization: `Bearer ${this.token}`,
      "Content-Type": "application/json",
    };
  }

  async loadProfile() {
    try {
      const response = await fetch("/api/sellers/profile", {
        headers: this.headers,
      });

      if (!response.ok) throw new Error();

      const profile = await response.json();

      this.profileTarget.innerHTML = "";
      this.profileTarget.appendChild(this.renderProfile(profile));
    } catch {
      localStorage.removeItem("token");
      window.location.href = "/account";
    }
  }

  renderProfile(profile) {
    const node = this.profileTemplateTarget.content.firstElementChild.cloneNode(true);

    this.setInput(node, "firstName", profile.firstName);
    this.setInput(node, "lastName", profile.lastName);
    this.setInput(node, "email", profile.email);
    this.setInput(node, "phone", profile.phone);
    this.setInput(node, "address", profile.address);
    this.setInput(node, "postalCode", profile.postalCode);
    this.setInput(node, "city", profile.city);

    return node;
  }

  setInput(root, name, value) {
    const input = root.querySelector(`[data-input="${name}"]`);
    if (input) input.value = value ?? "";
  }

  async updateProfile(event) {
    event.preventDefault();

    const form = event.currentTarget;

    const payload = {
      firstName: form.firstName.value?.trim() || null,
      lastName: form.lastName.value?.trim() || null,
      email: form.email.value?.trim() || null,
      phone: form.phone.value?.trim() || null,
      address: form.address.value?.trim() || null,
      postalCode: form.postalCode.value?.trim() || null,
      city: form.city.value?.trim() || null,
    };

    try {
      const response = await fetch("/api/sellers/profile", {
        method: "PUT",
        headers: this.headers,
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const err = await response.json().catch(() => null);

        // Si ton API renvoie violations (Symfony validator)
        if (err?.violations?.length) {
          toastr.error(err.violations.map((v) => `${v.propertyPath} : ${v.message}`).join("<br/>"));
        } else {
          toastr.error("Erreur lors de la mise à jour du profil");
        }
        return;
      }

      toastr.success("Profil mis à jour avec succès");
      // Optionnel: recharger pour afficher les valeurs normalisées côté API
      await this.loadProfile();
    } catch {
      toastr.error("Erreur réseau");
    }
  }

  async deleteAccount() {
    if (!confirm("Supprimer définitivement votre compte ?")) return;

    try {
      const response = await fetch("/api/sellers/profile", {
        method: "DELETE",
        headers: this.headers,
      });

      if (!response.ok) {
        toastr.error("Erreur lors de la suppression du compte");
        return;
      }

      localStorage.removeItem("token");
      window.location.href = "/";
    } catch {
      toastr.error("Erreur réseau");
    }
  }
}
