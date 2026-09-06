import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["btn", "error"];

  static values = {
    calculateUrl: String,   // "/api/estimations/calculate"
    createUrl: String,      // "/api/seller/vehicles/create-from-estimation"
    resultRedirect: String, // "/seller/estimation/result"
    accountUrl: String,     // "/account"
  };

  async submit(event) {
    event.preventDefault();
    console.log("estimation-flow submit triggered");

    const token = localStorage.getItem("token");

    //  Si pas de token, redirection vers login avec un flag pour revenir à la page résultat après login
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

      // CALCULATE => création de l’estimation et récupération du token d’estimation
      const calculateRes = await fetch(this.calculateUrlValue, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });

      // Vérification du content-type pour éviter de parser une page HTML (ex: redirection vers /account si token expiré)
      const ct1 = calculateRes.headers.get("content-type") || "";
      if (!ct1.includes("application/json")) {
        const text = await calculateRes.text().catch(() => "");
        console.error("calculate non-json response:", calculateRes.status, text.slice(0, 200));
        throw new Error("Réponse inattendue (non JSON). L’API est probablement protégée et vous a redirigé vers /account.");
      }

      // Si token expiré => retour /account
      if (calculateRes.status === 401) {
        localStorage.removeItem("token");
        sessionStorage.setItem(
          "after_login_redirect",
          this.resultRedirectValue || "/seller/estimation/result"
        );
        window.location.href = this.accountUrlValue || "/account";
        return;
      }

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

      // CREATE-FROM-ESTIMATION => création du véhicule à partir de l’estimation (vérifie que l’estimation est valide et appartient bien à l’utilisateur grâce au token d’estimation)
      const createRes = await fetch(this.createUrlValue, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": `Bearer ${token}`,
        },
        body: JSON.stringify({ estimation_token: estimationToken }),
      });

      // Vérification du content-type pour éviter de parser une page HTML (ex: redirection vers /account si token expiré)
      const ct2 = createRes.headers.get("content-type") || "";
      if (!ct2.includes("application/json")) {
        const text = await createRes.text().catch(() => "");
        console.error("create non-json response:", createRes.status, text.slice(0, 200));
        throw new Error("Réponse inattendue (non JSON). L’API est probablement protégée et vous a redirigé vers /account.");
      }

      // Si token expiré => retour /account
      if (createRes.status === 401) {
        localStorage.removeItem("token")
        sessionStorage.setItem(
          "after_login_redirect",
          this.resultRedirectValue || "/seller/estimation/result"
        );
        window.location.href = this.accountUrlValue || "/account";
        return;
      }

      if (!createRes.ok) {
        const apiErr = await this.parseError(createRes);
        console.error("create-from-estimation failed:", createRes.status, apiErr);
        throw new Error(this.userMessage(createRes.status, apiErr, "create"));
      }

      const created = await createRes.json();

      // Stocker le résultat pour la page result
      sessionStorage.setItem("estimationResult", JSON.stringify(created));

      // Nettoyage du redirect “en attente”
      sessionStorage.removeItem("after_login_redirect");

      // Redirection vers la page de résultat de l’estimation
      window.location.href = this.resultRedirectValue;

    } catch (e) {
      console.error(e);
      this.setError(e?.message || "Une erreur est survenue.");
    } finally {
      this.setLoading(false);
    }
  }

  // Redirection vers login si pas de token avant de lancer le flow d’estimation
  async parseError(res) {
    const contentType = res.headers.get("content-type") || "";
    if (contentType.includes("application/json")) {
      const json = await res.json().catch(() => null);
      return {
        type: "json",
        json,
        message: json?.message || json?.detail || null,
        raw: json,
      };
    }
    const text = await res.text().catch(() => "");
    return { type: "text", text, message: text || null, raw: text };
  }

  // Génère un message utilisateur à partir du status HTTP et du message d’erreur de l’API
  userMessage(status, apiErr, step) {
    const rawMsg = (apiErr?.message || "").toString();

    if (status === 401) return "Votre session API a expiré. Veuillez vous reconnecter.";
    if (status === 403) return "Accès refusé : connectez-vous avec un compte vendeur.";

    if (status === 409) {
      if (rawMsg && rawMsg.length < 200) return rawMsg;
      if (step === "calculate") return "Un véhicule avec cette plaque existe déjà sur votre compte.";
      return "Conflit : ce véhicule est peut-être déjà lié à un autre vendeur.";
    }

    if (status === 422) return rawMsg || "Certains champs sont invalides. Vérifiez le formulaire.";
    return rawMsg || `Une erreur est survenue (HTTP ${status}).`;
  }

  
  // transforme le formulaire HTML en objet JavaScript pour l’envoyer à l'API.
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