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
    "color"
  ];

  connect() {
    const raw = sessionStorage.getItem("plate_prefill");
    if (!raw) return;

    const { plate, found, vehicle } = JSON.parse(raw);

    if (this.hasPlateTarget) this.plateTarget.value = plate || "";

    if (found && vehicle) {
      if (this.hasVinTarget) this.vinTarget.value = vehicle.vin ?? "";
      if (this.hasBrandTarget) this.brandTarget.value = vehicle.brand ?? "";
      if (this.hasModelTarget) this.modelTarget.value = vehicle.model ?? "";
      if (this.hasVersionTarget) this.versionTarget.value = vehicle.version ?? "";
      if (this.hasEnergyTarget) this.energyTarget.value = vehicle.energy ?? "";
      if (this.hasRegistrationDateTarget) this.registrationDateTarget.value = vehicle.registrationDate ?? "";
      if (this.hasHorsePowerTarget) this.horsePowerTarget.value = vehicle.horsePower ?? "";
      if (this.hasFiscalPowerTarget)this.fiscalPowerTarget.value = vehicle.fiscalPower ?? "";
      if (this.hasGearBoxTarget) this.gearBoxTarget.value = vehicle.gearBox ?? "";
      if (this.hasDoorsTarget) this.doorsTarget.value = vehicle.doors ?? "";
      if (this.hasSeatsTarget) this.seatsTarget.value = vehicle.seats ?? "";
      if (this.hasBodyTypeTarget) this.bodyTypeTarget.value = vehicle.bodyType ?? "";
      if (this.hasWeightKgTarget) this.weightKgTarget.value = vehicle.weightKg ?? "";
      if (this.hasColorTarget) this.colorTarget.value = vehicle.color ?? "";
    }
  }
}
