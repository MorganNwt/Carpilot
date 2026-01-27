import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["price", "meta"];

  connect() {
    const raw = sessionStorage.getItem("estimationResult");
    if (!raw) return;

    const data = JSON.parse(raw);

    // d’après ton JSON Postman : data.estimation.estimated_price
    const price = data?.estimation?.estimated_price;
    const plate = data?.plate ?? "";
    const brand = data?.brand ?? "";
    const model = data?.model ?? "";

    if (price != null) {
      this.priceTarget.textContent = `${price.toLocaleString("fr-FR")} €`;
    }

    this.metaTarget.textContent = [plate, brand, model].filter(Boolean).join(" • ");
  }
}
