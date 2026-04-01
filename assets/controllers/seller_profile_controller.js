import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["profile", "profileTemplate"];

  async connect() {
    const token = localStorage.getItem("token");

    if (!token) {
      window.location.href = "/account";
      return;
    }

    await this.loadProfile();
  }

  get headers() {
    const token = localStorage.getItem("token");

    return {
      Accept: "application/json",
      "Content-Type": "application/json",
      Authorization: `Bearer ${token}`,
    };
  }

  async safeJson(url, options = {}) {
    const token = localStorage.getItem("token");

    if (!token) {
      window.location.href = "/account";
      return null;
    }

    try {
      const res = await fetch(url, {
        ...options,
        headers: {
          ...this.headers,
          ...(options.headers || {}),
        },
      });

      if (res.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/account";
        return null;
      }

      if (!res.ok) {
        const err = await res.json().catch(() => null);
        throw new Error(err?.message ?? `HTTP ${res.status}`);
      }

      const txt = await res.text();
      return txt ? JSON.parse(txt) : {};
    } catch (e) {
      console.error("API error:", url, e);
      return null;
    }
  }

  async loadProfile() {
    const profile = await this.safeJson("/api/sellers/profile", { method: "GET" });
    if (!profile) return;

    this.profileTarget.innerHTML = "";
    this.profileTarget.appendChild(this.renderProfile(profile));
  }

  renderProfile(profile) {
    const node = this.profileTemplateTarget.content.firstElementChild.cloneNode(true);

    this.setField(node, "firstName", profile.firstName);
    this.setField(node, "lastName", profile.lastName);
    this.setField(node, "email", profile.email);
    this.setField(node, "phone", profile.phone);
    this.setField(node, "address", profile.address);
    this.setField(node, "postalCode", profile.postalCode);
    this.setField(node, "city", profile.city);

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

  setField(root, name, value) {
  const el = root.querySelector(`[data-field="${name}"]`);
  if (el) el.textContent = value ?? "";
}

toggleEdit(event) {
  const container = event.currentTarget.closest("[data-profile-card]");
  if (!container) return;

  const view = container.querySelector("[data-view]");
  const edit = container.querySelector("[data-edit]");
  if (!view || !edit) return;

  view.classList.toggle("hidden");
  edit.classList.toggle("hidden");
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
      const res = await fetch("/api/sellers/profile", {
        method: "PUT",
        headers: this.headers,
        body: JSON.stringify(payload),
      });

      if (res.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/account";
        return;
      }

      if (!res.ok) {
        const err = await res.json().catch(() => null);

        if (err?.violations?.length) {
          toastr.error(
            err.violations
              .map((v) => `${v.propertyPath} : ${v.message}`)
              .join("<br/>")
          );
        } else {
          toastr.error(err?.message ?? "Erreur lors de la mise à jour du profil");
        }
        return;
      }

      toastr.success("Profil mis à jour avec succès");
      await this.loadProfile();
    } catch {
      toastr.error("Erreur réseau");
    }
  }

  async deleteAccount() {
    if (!confirm("Supprimer définitivement votre compte ?")) return;

    try {
      const res = await fetch("/api/sellers/profile", {
        method: "DELETE",
        headers: this.headers,
      });

      if (res.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/account";
        return;
      }

      if (!res.ok && res.status !== 204) {
        const err = await res.json().catch(() => null);
        toastr.error(err?.message ?? "Erreur lors de la suppression du compte");
        return;
      }

      localStorage.removeItem("token");
      sessionStorage.clear();
      window.location.href = "/logout";
    } catch {
      toastr.error("Erreur réseau");
    }
  }
}