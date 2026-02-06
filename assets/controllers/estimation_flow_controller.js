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
      sessionStorage.setItem(
        "after_login_redirect",
        this.resultRedirectValue || "/seller/estimation/result"
      );
      window.location.href = this.accountUrlValue || "/account";
      return;
    }

    this.setLoading(true);
    this.setError("");

    try {
      const form = event.currentTarget;
      const payload = this.formToJson(form);

      // ===== 1) CALCULATE =====
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
        const apiErr = await this.parseError(calculateRes);
        console.error("calculate failed:", calculateRes.status, apiErr);
        throw new Error(this.userMessage(calculateRes.status, apiErr, "calculate"));
      }

      const calculateJson = await calculateRes.json();
      const estimationToken = calculateJson?.estimation_token;

      if (!estimationToken) {
        console.error("calculate response missing estimation_token:", calculateJson);
        throw new Error("Réponse serveur invalide : token d’estimation manquant.");
      }

      // ===== 2) CREATE-FROM-ESTIMATION =====
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
        const apiErr = await this.parseError(createRes);
        console.error("create-from-estimation failed:", createRes.status, apiErr);
        throw new Error(this.userMessage(createRes.status, apiErr, "create"));
      }

      const created = await createRes.json();

      sessionStorage.setItem("estimationResult", JSON.stringify(created));
      window.location.href = this.resultRedirectValue;

    } catch (e) {
      console.error(e);
      this.setError(e?.message || "Une erreur est survenue.");
    } finally {
      this.setLoading(false);
    }
  }

  // ============= Helpers erreurs (robuste JSON / texte) =============
  async parseError(res) {
    const contentType = res.headers.get("content-type") || "";

    // Essaie JSON
    if (contentType.includes("application/json")) {
      const json = await res.json().catch(() => null);
      return {
        type: "json",
        json,
        message: json?.message || json?.detail || null,
        raw: json,
      };
    }

    // Sinon texte
    const text = await res.text().catch(() => "");
    return {
      type: "text",
      text,
      message: text || null,
      raw: text,
    };
  }

  userMessage(status, apiErr, step) {
    const rawMsg = (apiErr?.message || "").toString();

    // 🔒 Auth / droits
    if (status === 401) {
      return "Votre session a expiré. Veuillez vous reconnecter.";
    }

    // 403 = connecté mais pas le bon rôle (Agent/Admin sur flow vendeur)
    if (
      status === 403 ||
      rawMsg.includes("Access Denied") ||
      rawMsg.includes("ROLE_SELLER") ||
      rawMsg.includes("Seller") // parfois: "Seller expected"
    ) {
      return "Accès refusé : connectez-vous avec un compte vendeur pour enregistrer un véhicule.";
    }

    // Conflit métier (ex: déjà associé)
    if (status === 409) {
      // si backend renvoie un message précis, on le garde
      if (rawMsg && rawMsg.length < 200) return rawMsg;

      // fallback selon l'étape
      if (step === "calculate") {
        return "Un véhicule avec cette plaque existe déjà sur votre compte. Ouvrez l’offre en cours ou mettez-la à jour.";
      }
      return "Ce véhicule ne peut pas être enregistré (conflit). Il est peut-être déjà lié à un autre vendeur.";
    }

    // Validation
    if (status === 422) {
      return rawMsg || "Certains champs sont invalides. Vérifiez le formulaire.";
    }

    // Fallback générique (si backend a déjà un message -> on l'affiche)
    return rawMsg || `Une erreur est survenue (HTTP ${status}).`;
  }

  // ============= Form parsing =============
  formToJson(form) {
    const fd = new FormData(form);
    const obj = Object.fromEntries(fd.entries());

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
      registrationDate: obj.registrationDate ?? "",
    };
  }

  // ============= UI =============
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
