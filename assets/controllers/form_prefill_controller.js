import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
    "title",   
    "subtitle",       
    "plate",
    "vin",
    "brand",
    "model",
    "version",
    "energy",
    "registrationDate",
    "horsePower",
    "fiscalPower",
    "gearBox",
    "doors",
    "seats",
    "bodyType",
    "weightKg",
    "color",
    "mileage",
  ];

  static values = {
    endpoint: String, // "/mock/plate-lookup"
  };

  async connect() {
    const raw = sessionStorage.getItem("plate_prefill");
    if (!raw) return;

    const data = JSON.parse(raw);
    const plate = data?.plate;

    if (this.hasPlateTarget) this.plateTarget.value = plate || "";

    //  état initial du message (avant lookup)
    this.setFoundState(!!data?.found);

    // si on a déjà le véhicule (cas connecté dès le départ)
    if (data?.vehicle) {
      this.setFoundState(true);
      this.fill(data.vehicle);
      return;
    }

    // sinon (cas plaque -> login -> retour), on relance le lookup maintenant
    if (!plate) return;

    //  si endpoint non fourni, on stoppe
    if (!this.hasEndpointValue || !this.endpointValue) return;

    const vehicle = await this.fetchVehicle(plate);
    const found = !!vehicle;

    sessionStorage.setItem(
      "plate_prefill",
      JSON.stringify({
        ...data,
        found,
        vehicle: found ? vehicle : null,
      })
    );

    // Message dynamique après lookup
    this.setFoundState(found);

    if (found) this.fill(vehicle);
  }

  // Set message trouvé / non trouvé
  setFoundState(found) {
    if (found) {
      if (this.hasTitleTarget) this.titleTarget.textContent = "Votre véhicule a bien été retrouvé !";
      if (this.hasSubtitleTarget) this.subtitleTarget.textContent = "Veuillez vérifier les informations saisies avant de continuer :";
      return;
    }

    if (this.hasTitleTarget) this.titleTarget.textContent = "Votre véhicule n’a pas été retrouvé.";
    if (this.hasSubtitleTarget) this.subtitleTarget.textContent = "Vous devez compléter les informations manuellement pour continuer :";
  }


  // ======================
  // Logique de lookup + redirection
  // ======================

  async fetchVehicle(plate) {
  const headers = { Accept: "application/json" };

  const token = localStorage.getItem("token");
  if (token) headers["Authorization"] = `Bearer ${token}`;

  //  Essai avec la plaque formatée (ex: EZ-112-TT)
  try {
    let url = `${this.endpointValue}/${encodeURIComponent(plate)}`;
    let res = await fetch(url, { headers, credentials: "same-origin" });
    let json = await res.json().catch(() => null);

    const found1 = !!json && json.error === false && !!json.data;
    if (found1) return json.data;

    // Fallback avec la plaque compacte (ex: EZ112TT)
    const compact = this.compactPlate(plate);
    if (compact && compact !== plate) {
      url = `${this.endpointValue}/${encodeURIComponent(compact)}`;
      res = await fetch(url, { headers, credentials: "same-origin" });
      json = await res.json().catch(() => null);

      const found2 = !!json && json.error === false && !!json.data;
      if (found2) return json.data;
    }

    return null;
  } catch (e) {
    return null;
  }
}

  // helper compact (keep only letters and numbers, uppercase, no space)
  compactPlate(v) {
    return (v || "")
      .toUpperCase()
      .trim()
      .replace(/[^A-Z0-9\s-]/g, "")
      .replace(/[\s-]+/g, "");
  }

  fill(vehicle) {
    if (this.hasVinTarget) this.vinTarget.value = vehicle.vin ?? "";
    if (this.hasBrandTarget) this.brandTarget.value = vehicle.brand ?? "";
    if (this.hasModelTarget) this.modelTarget.value = vehicle.model ?? "";
    if (this.hasVersionTarget) this.versionTarget.value = vehicle.version ?? "";
    if (this.hasEnergyTarget) this.energyTarget.value = vehicle.energy ?? "";
    if (this.hasRegistrationDateTarget) this.registrationDateTarget.value = vehicle.registrationDate ?? "";
    if (this.hasHorsePowerTarget) this.horsePowerTarget.value = vehicle.horsePower ?? "";
    if (this.hasFiscalPowerTarget) this.fiscalPowerTarget.value = vehicle.fiscalPower ?? "";
    if (this.hasGearBoxTarget) this.gearBoxTarget.value = vehicle.gearBox ?? "";
    if (this.hasDoorsTarget) this.doorsTarget.value = vehicle.doors ?? "";
    if (this.hasSeatsTarget) this.seatsTarget.value = vehicle.seats ?? "";
    if (this.hasBodyTypeTarget) this.bodyTypeTarget.value = vehicle.bodyType ?? "";
    if (this.hasWeightKgTarget) this.weightKgTarget.value = vehicle.weightKg ?? "";
    if (this.hasColorTarget) this.colorTarget.value = vehicle.color ?? "";
    if (this.hasMileageTarget) this.mileageTarget.value = vehicle.mileage ?? vehicle.mileageKm ?? "";
  }
}