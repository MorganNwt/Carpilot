import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["vehicles", "vehicleTemplate"];

  connect() {
    const token = localStorage.getItem("token");
    if (!token) {
      window.location.href = "/account";
      return;
    }
    this.loadVehicles();
  }

  get token() {
    return localStorage.getItem("token");
  }

  get headers() {
    return {
      Authorization: `Bearer ${this.token}`,
      "Content-Type": "application/json",
      Accept: "application/json",
    };
  }

  formatDate(dateString) {
    if (!dateString) return "—";
    const date = new Date(dateString);
    return date.toLocaleDateString("fr-FR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  toggleEdit(event) {
    const container = event.currentTarget.closest("[data-id]");
    if (!container) return;

    // bloquer ouverture si véhicule verrouillé
    if (container.dataset.vehicleLocked === "1") {
      toastr.error("Modification impossible : dossier en cours de traitement ou clôturé.");
      return;
    }

    const view = container.querySelector("[data-view]");
    const edit = container.querySelector("[data-edit]");
    if (!view || !edit) return;

    const isEditing = !edit.classList.contains("hidden");
    view.classList.toggle("hidden");
    edit.classList.toggle("hidden");

    if (isEditing) {
      edit.reset();
    }
  }

  async loadVehicles() {
    try {
      const response = await fetch("/api/seller/vehicles", { headers: this.headers });
      if (!response.ok) throw new Error();

      const vehicles = await response.json();

      if (!vehicles?.length) {
        this.vehiclesTarget.innerHTML = `<p class="text-gray-400">Aucun véhicule enregistré</p>`;
        return;
      }

      this.vehiclesTarget.innerHTML = "";

      const frag = document.createDocumentFragment();
      for (const vehicle of vehicles) {
        frag.appendChild(this.renderVehicle(vehicle));
      }
      this.vehiclesTarget.appendChild(frag);
    } catch {
      localStorage.removeItem("token");
      window.location.href = "/account";
    }
  }

  renderVehicle(vehicle) {
    const node = this.vehicleTemplateTarget.content.firstElementChild.cloneNode(true);

    // Container / form ids
    node.dataset.id = String(vehicle.id ?? "");
    const form = node.querySelector("form[data-edit]");
    if (form) form.dataset.id = String(vehicle.id ?? "");

    // estimation id for offer endpoint
    node.dataset.estimationId = vehicle?.estimation?.id ? String(vehicle.estimation.id) : "";

    // can edit offer from API (derived from status)
    const canEditOffer = !!vehicle?.estimation?.can_edit_offer;
    node.dataset.canEditOffer = canEditOffer ? "1" : "0";

    // Fill fields
    this.setText(node, "plate", vehicle.plate ?? "—");
    this.setText(node, "brand", vehicle.brand ?? "");
    this.setText(node, "model", vehicle.model ?? "");
    this.setText(node, "createdAt", this.formatDate(vehicle.createdAt));

    this.setText(node, "energy", vehicle.energy ?? "—");
    this.setText(node, "gearBox", vehicle.gearBox ?? "—");
    this.setText(node, "horsePower", vehicle.horsePower != null ? `${vehicle.horsePower} ch` : "—");
    this.setText(node, "doors", vehicle.doors != null ? `${vehicle.doors} portes` : "—");
    this.setText(node, "seats", vehicle.seats != null ? `${vehicle.seats} places` : "—");
    this.setText(node, "bodyType", vehicle.bodyType ?? "—");
    this.setText(node, "color", vehicle.color ? `Couleur : ${vehicle.color}` : "Couleur : —");

    // Estimation display (estimated_price)
    const estEl = node.querySelector('[data-field="estimationValue"]');
    const estimatedPrice = vehicle?.estimation?.estimated_price;

    if (estEl) {
      if (estimatedPrice != null) {
        estEl.textContent = `${estimatedPrice} €`;
        estEl.classList.remove("text-gray-400");
        estEl.classList.add("text-green-600");
      } else {
        estEl.textContent = "Aucune estimation";
        estEl.classList.remove("text-green-600");
        estEl.classList.add("text-gray-400");
      }
    }

    // Offer input + button lock
    const offerInput = node.querySelector('[data-input="offerPrice"]');
    const offerBtn = node.querySelector('[data-action*="seller-vehicle#saveOffer"]');

    const hasEstimation = !!vehicle?.estimation?.id;
    const offerLocked = !hasEstimation || !canEditOffer;

    //  status estimation
    const status = vehicle?.estimation?.status ?? null;

    //  verrouillage édition véhicule si dossier pris en charge / clôturé
    const vehicleLockedStatuses = ["in_review", "rejected", "transaction_completed", "cancelled"];
    const vehicleLocked = status ? vehicleLockedStatuses.includes(status) : false;

    // on stocke dans le DOM pour toggleEdit()
    node.dataset.vehicleLocked = vehicleLocked ? "1" : "0";

    // Offer input
    if (offerInput) {
      offerInput.value = vehicle?.estimation?.offer_price ?? "";
      offerInput.disabled = offerLocked;
      offerInput.classList.toggle("opacity-60", offerLocked);
      offerInput.classList.toggle("cursor-not-allowed", offerLocked);

      if (offerLocked) {
        if (status === "rejected") offerInput.placeholder = "Offre refusée";
        else if (status === "transaction_completed") offerInput.placeholder = "Offre acceptée";
        else offerInput.placeholder = "Offre verrouillée";
      }
    }

    // Offer button label
    if (offerBtn) {
      offerBtn.disabled = offerLocked;
      offerBtn.classList.toggle("opacity-60", offerLocked);
      offerBtn.classList.toggle("cursor-not-allowed", offerLocked);

      const hasOffer =
        vehicle?.estimation?.offer_price != null &&
        String(vehicle.estimation.offer_price).trim() !== "";

      if (status === "rejected") {
        offerBtn.textContent = "Offre refusée";
      } else if (status === "transaction_completed") {
        offerBtn.textContent = "Offre acceptée";
      } else if (offerLocked) {
        offerBtn.textContent = "Verrouillé";
      } else {
        offerBtn.textContent = hasOffer ? "Mettre à jour" : "Valider";
      }
    }

    // ✅ bouton "Modifier" (toggleEdit)
    const editBtn = node.querySelector('[data-action*="seller-vehicle#toggleEdit"]');

    if (editBtn) {
      editBtn.disabled = vehicleLocked;
      editBtn.classList.toggle("opacity-60", vehicleLocked);
      editBtn.classList.toggle("cursor-not-allowed", vehicleLocked);

      if (vehicleLocked) {
        editBtn.textContent = "Verrouillé";
        editBtn.title = "Modification impossible : dossier en cours de traitement ou clôturé.";
      } else {
        editBtn.textContent = "Modifier";
        editBtn.removeAttribute("title");
      }
    }

    // ✅ Si verrouillé, forcer l'affichage en mode VIEW
    if (vehicleLocked) {
      const view = node.querySelector("[data-view]");
      const edit = node.querySelector("[data-edit]");
      if (view && edit) {
        view.classList.remove("hidden");
        edit.classList.add("hidden");
      }
    }

    // Prefill edit inputs
    this.setInput(node, "plate", vehicle.plate);
    this.setInput(node, "vin", vehicle.vin);
    this.setInput(node, "brand", vehicle.brand);
    this.setInput(node, "model", vehicle.model);
    this.setInput(node, "version", vehicle.version);
    this.setInput(node, "energy", vehicle.energy);
    this.setInput(node, "horsePower", vehicle.horsePower);
    this.setInput(node, "fiscalPower", vehicle.fiscalPower);
    this.setInput(node, "gearBox", vehicle.gearBox);
    this.setInput(node, "doors", vehicle.doors);
    this.setInput(node, "seats", vehicle.seats);
    this.setInput(node, "bodyType", vehicle.bodyType);
    this.setInput(node, "color", vehicle.color);
    this.setInput(node, "weightKg", vehicle.weightKg);

    return node;
  }

  setText(root, field, value) {
    const el = root.querySelector(`[data-field="${field}"]`);
    if (el) el.textContent = value ?? "";
  }

  setInput(root, name, value) {
    const input = root.querySelector(`[data-input="${name}"]`);
    if (input) input.value = value ?? "";
  }

  // submit offer_price for an estimation
  async saveOffer(event) {
    const container = event.currentTarget.closest("[data-id]");
    if (!container) return;

    const estimationId = container.dataset.estimationId;
    if (!estimationId) {
      toastr.error("Aucune estimation associée à ce véhicule");
      return;
    }

    const canEdit = container.dataset.canEditOffer === "1";
    if (!canEdit) {
      toastr.error("Offre verrouillée : elle est en cours de traitement ou clôturée.");
      return;
    }

    const input = container.querySelector('[data-input="offerPrice"]');
    const value = input?.value?.trim();

    if (!value) {
      toastr.error("Veuillez saisir un prix");
      return;
    }

    const normalized = value.replace(",", ".");
    const num = Number.parseFloat(normalized);
    if (!Number.isFinite(num) || num <= 0) {
      toastr.error("Veuillez saisir un prix valide");
      return;
    }

    // DTO attend une string
    const payload = { offer_price: String(normalized) };

    try {
      const response = await fetch(`/api/seller/estimations/${estimationId}/offer`, {
        method: "PUT",
        headers: this.headers,
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const contentType = response.headers.get("content-type") || "";
        const err = contentType.includes("application/json")
          ? await response.json().catch(() => null)
          : await response.text().catch(() => null);

        toastr.error(err?.message ?? "Erreur lors de l'envoi de l'offre");
        return;
      }

      toastr.success("Offre enregistrée");
      await this.loadVehicles();
    } catch {
      toastr.error("Erreur réseau");
    }
  }

  async updateVehicle(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const id = form.dataset.id;
    if (!id) return;

    const container = form.closest("[data-id]");
    if (container?.dataset.vehicleLocked === "1") {
      toastr.error("Modification impossible : dossier en cours de traitement ou clôturé.");
      return;
    }

    const raw = Object.fromEntries(new FormData(form));

    const payload = {
      plate: raw.plate?.trim() || null,
      vin: raw.vin?.trim() || null,
      brand: raw.brand?.trim() || null,
      model: raw.model?.trim() || null,
      version: raw.version?.trim() || null,
      energy: raw.energy?.trim() || null,
      gearBox: raw.gearBox?.trim() || null,
      bodyType: raw.bodyType?.trim() || null,
      color: raw.color?.trim() || null,
      registrationDate: raw.registrationDate?.trim() || null,

      horsePower: raw.horsePower?.trim() ? Number.parseInt(raw.horsePower, 10) : null,
      fiscalPower: raw.fiscalPower?.trim() ? Number.parseFloat(raw.fiscalPower) : null,
      doors: raw.doors?.trim() ? Number.parseInt(raw.doors, 10) : null,
      seats: raw.seats?.trim() ? Number.parseInt(raw.seats, 10) : null,
      weightKg: raw.weightKg?.trim() ? Number.parseInt(raw.weightKg, 10) : null,
    };

    try {
      const response = await fetch(`/api/seller/vehicles/update/${id}`, {
        method: "PUT",
        headers: this.headers,
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const err = await response.json().catch(() => null);
        if (err?.violations?.length) {
          toastr.error(err.violations.map((v) => `${v.propertyPath} : ${v.message}`).join("<br/>"));
        } else {
          toastr.error(err?.message ?? "Erreur lors de la mise à jour du véhicule");
        }
        return;
      }

      toastr.success("Véhicule mis à jour avec succès");
      await this.loadVehicles();
    } catch {
      toastr.error("Erreur lors de la mise à jour du véhicule");
    }
  }

  async deleteVehicle(event) {
    const container = event.currentTarget.closest("[data-id]");
    const id = container?.dataset?.id;

    if (!id) {
      toastr.error("Impossible de supprimer : identifiant introuvable");
      return;
    }

    if (container?.dataset.vehicleLocked === "1") {
      toastr.error("Suppression impossible : dossier en cours de traitement ou clôturé.");
      return;
    }

    if (!confirm("Supprimer ce véhicule ?")) return;

    try {
      const response = await fetch(`/api/seller/vehicles/${id}`, {
        method: "DELETE",
        headers: this.headers,
      });

      if (!response.ok) {
        const err = await response.json().catch(() => null);
        toastr.error(err?.message ?? "Erreur lors de la suppression du véhicule");
        return;
      }

      toastr.success("Véhicule supprimé");
      await this.loadVehicles();
    } catch {
      toastr.error("Erreur lors de la suppression du véhicule");
    }
  }
}
