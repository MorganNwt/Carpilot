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

    let data;
    try {
      data = JSON.parse(raw);
    } catch {
      return;
    }

    const plate = data?.plate;

    if (this.hasPlateTarget) {
      this.plateTarget.value = plate || "";
    }

    this.setFoundState(!!data?.found);

    if (data?.vehicle) {
      this.setFoundState(true);
      this.fill(data.vehicle);
      return;
    }

    if (!plate) return;
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

    this.setFoundState(found);

    if (found) {
      this.fill(vehicle);
    }
  }

  setFoundState(found) {
    if (found) {
      if (this.hasTitleTarget) {
        this.titleTarget.textContent = "Votre véhicule a bien été retrouvé !";
      }
      if (this.hasSubtitleTarget) {
        this.subtitleTarget.textContent = "Veuillez vérifier les informations saisies avant de continuer :";
      }
      return;
    }

    if (this.hasTitleTarget) {
      this.titleTarget.textContent = "Votre véhicule n’a pas été retrouvé.";
    }
    if (this.hasSubtitleTarget) {
      this.subtitleTarget.textContent = "Vous devez compléter les informations manuellement pour continuer :";
    }
  }

  async fetchVehicle(plate) {
    const headers = { Accept: "application/json" };

    const token = localStorage.getItem("token");
    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    try {
      let url = `${this.endpointValue}/${encodeURIComponent(plate)}`;
      let res = await fetch(url, { headers, credentials: "same-origin" });

      if (res.status === 401) {
        localStorage.removeItem("token");
        return null;
      }

      let json = await res.json().catch(() => null);
      const found1 = !!json && json.error === false && !!json.data;

      if (found1) {
        return json.data;
      }

      const compact = this.compactPlate(plate);
      if (compact && compact !== plate) {
        url = `${this.endpointValue}/${encodeURIComponent(compact)}`;
        res = await fetch(url, { headers, credentials: "same-origin" });

        if (res.status === 401) {
          localStorage.removeItem("token");
          return null;
        }

        json = await res.json().catch(() => null);
        const found2 = !!json && json.error === false && !!json.data;

        if (found2) {
          return json.data;
        }
      }

      return null;
    } catch {
      return null;
    }
  }

  compactPlate(value) {
    return (value || "")
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