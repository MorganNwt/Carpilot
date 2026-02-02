import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["btn", "error"];
  static values = {
    calculateUrl: String,   // "/api/estimations/calculate"
    createUrl: String,      // "/api/seller/vehicles/create-from-estimation"
    resultRedirect: String, // "/seller/estimation/result"
    accountUrl: String      // "/account"
  };

  async submit(event) {
    event.preventDefault();

    const token = localStorage.getItem("token");
    if (!token) {
      // sécurité : si on arrive ici non connecté
      sessionStorage.setItem("after_login_redirect", this.resultRedirectValue || "/seller/estimation/result");
      window.location.href = this.accountUrlValue || "/account";
      return;
    }

    this.setLoading(true);
    this.setError("");

    try {
      // construire payload depuis le form HTML
      const form = event.currentTarget;
      const payload = this.formToJson(form);

      // CALCULATE -> estimation_token
      const calculateRes = await fetch(this.calculateUrlValue, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Authorization": `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    if (!calculateRes.ok) {
      const contentType = calculateRes.headers.get("content-type") || "";
      let apiBody = null;

      try {
        apiBody = contentType.includes("application/json")
          ? await calculateRes.json()
          : await calculateRes.text();
      } catch (_) {
        apiBody = null;
      }

      console.error("calculate failed:", calculateRes.status, apiBody);

      // Message utilisateur plus clair selon le code
      if (calculateRes.status === 409) {
        throw new Error(
          (apiBody && apiBody.message) ||
          "Un véhicule avec cette plaque existe déjà sur votre compte. Ouvrez l’offre en cours ou mettez-la à jour."
        );
      }

      if (calculateRes.status === 422) {
        throw new Error(
          (apiBody && apiBody.message) ||
          "Certains champs sont invalides. Vérifiez le formulaire."
        );
      }

      if (calculateRes.status === 401 || calculateRes.status === 403) {
        throw new Error("Votre session a expiré. Veuillez vous reconnecter.");
      }

      throw new Error(
        (apiBody && apiBody.message) ||
        `Impossible de calculer l’estimation (HTTP ${calculateRes.status}).`
      );
    }

    const calculateJson = await calculateRes.json();
    const estimationToken = calculateJson?.estimation_token;

    if (!estimationToken) {
      console.error("calculate response missing estimation_token:", calculateJson);
      throw new Error("Réponse serveur invalide : token d’estimation manquant.");
    }

        // CREATE-FROM-ESTIMATION -> véhicule + estimation
        const createRes = await fetch(this.createUrlValue, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "Authorization": `Bearer ${token}`,
          },
          body: JSON.stringify({ estimation_token: estimationToken }),
        });

        if (!createRes.ok) {
          const contentType = createRes.headers.get("content-type") || "";
          let msg = `Erreur (${createRes.status}) lors de l’enregistrement.`;

          if (contentType.includes("application/json")) {
            const err = await createRes.json().catch(() => null);
            msg = err?.detail || err?.message || msg;
          } else {
            const text = await createRes.text().catch(() => "");
            if (text) msg = text;
          }

          console.error("create-from-estimation failed:", createRes.status, msg);
          throw new Error(msg);
        }

      const created = await createRes.json();

      //stocker le résultat pour la page suivante
      sessionStorage.setItem("estimationResult", JSON.stringify(created));

      // redirect vers la vue résultat
      window.location.href = this.resultRedirectValue;

    } catch (e) {
      console.error(e);
      this.setError(e.message || "Une erreur est survenue.");
    } finally {
      this.setLoading(false);
    }
  }

  formToJson(form) {
    // récupère tous les champs du form en un objet
    const fd = new FormData(form);
    const obj = Object.fromEntries(fd.entries());

    // conversions utiles (sinon tout arrive en string)
    const toInt = (v) => (v === "" || v == null ? null : parseInt(v, 10));
    const toFloat = (v) => (v === "" || v == null ? null : parseFloat(v));

    return {
      plate: obj.plate ?? "",
      vin: obj.vin ?? "",
      brand: obj.brand ?? "",
      model: obj.model ?? "",
      version: obj.version ?? null,
      energy: obj.energy ?? "",
      horsePower: toInt(obj.horsePower),
      fiscalPower: toFloat(obj.fiscalPower),
      gearBox: obj.gearBox ?? "",
      doors: toInt(obj.doors),
      seats: toInt(obj.seats),
      bodyType: obj.bodyType ?? "",
      weightKg: toInt(obj.weightKg),
      color: obj.color ?? "",
      mileage: toInt(obj.mileage),
      registrationDate: obj.registrationDate ?? "", // "YYYY-MM-DD"
    };
  }

  setLoading(isLoading) {
    if (this.hasBtnTarget) {
      this.btnTarget.disabled = isLoading;
      this.btnTarget.classList.toggle("opacity-60", isLoading);
      this.btnTarget.classList.toggle("cursor-not-allowed", isLoading);
    }
  }

  setError(msg) {
    if (this.hasErrorTarget) {
      this.errorTarget.textContent = msg || "";
      this.errorTarget.classList.toggle("hidden", !msg);
    }
  }
}
