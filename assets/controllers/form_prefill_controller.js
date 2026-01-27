import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = [
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

    // si on a déjà le véhicule (cas connecté dès le départ)
    if (data?.vehicle) {
      this.fill(data.vehicle);
      return;
    }

    // sinon (cas plaque -> login -> retour), on relance le lookup maintenant
    if (!plate) return;

    // ✅ si endpoint non fourni, on stoppe
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

    if (found) this.fill(vehicle);
  }

  async fetchVehicle(plate) {
    const url = `${this.endpointValue}/${encodeURIComponent(plate)}`;

    const headers = { Accept: "application/json" };

    const token = localStorage.getItem("token");
    if (token) headers["Authorization"] = `Bearer ${token}`;

    try {
      const res = await fetch(url, { headers, credentials: "same-origin" });
      const json = await res.json().catch(() => null);

      // Format mock attendu: { error: false, data: {...} }
      const found = !!json && json.error === false && !!json.data;
      return found ? json.data : null;
    } catch (e) {
      return null;
    }
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
