import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["registerForm", "registerError"];

  async register(event) {
    event.preventDefault();
    this.registerErrorTarget.textContent = "";

    const form = this.registerFormTarget;

    // ✅ Validations champs (hors mot de passe)
    const validators = [
      { field: "lastName", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(v), msg: "Nom invalide." },
      { field: "firstName", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(v), msg: "Prénom invalide." },
      { field: "email", ok: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v), msg: "Email invalide." },
      { field: "phone", ok: (v) => /^(?:\+33|0)[1-9](?:[ .-]?\d{2}){4}$/.test(v), msg: "Téléphone invalide." },
      { field: "postalCode", ok: (v) => /^\d{5}$/.test(v), msg: "Code postal invalide." },
      { field: "city", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,100}$/.test(v), msg: "Ville invalide." },
      { field: "address", ok: (v) => String(v).trim().length >= 5, msg: "Adresse trop courte." },
    ];

    for (const r of validators) {
      const value = String(form[r.field]?.value ?? "").trim();
      if (!r.ok(value)) {
        this.registerErrorTarget.textContent = r.msg;
        return;
      }
    }

    // ✅ Validation mot de passe
    const password = form.querySelector('[name="password"]').value;
    const passwordConfirm = form.querySelector('[name="passwordConfirm"]').value;

    if (password !== passwordConfirm) {
      this.registerErrorTarget.textContent = "Les mots de passe ne correspondent pas";
      return;
    }

    const specialsCount = (password.match(/[^a-zA-Z0-9]/g) || []).length;
    if (password.length < 13 || !/[A-Z]/.test(password) || !/[0-9]/.test(password) || specialsCount < 2) {
      this.registerErrorTarget.textContent =
        "Mot de passe invalide : 13 caractères, 1 majuscule, 1 chiffre et 2 caractères spéciaux requis.";
      return;
    }

    // ✅ RGPD
    const rgpdConsent = form.querySelector('[name="rgpdConsent"]').checked;
    if (!rgpdConsent) {
      this.registerErrorTarget.textContent =
        "Vous devez accepter la politique de confidentialité (RGPD).";
      return;
    }

    const generic =
      "Une erreur s’est produite pendant l’inscription. Veuillez recommencer. Nous nous excusons pour la gêne occasionnée.";

    const email = form.email.value.trim();

    const agencyMeta = document.querySelector('meta[name="selected-agency-id"]');
    const agencyId = Number(agencyMeta?.content || 0);

if (!agencyId) {
  this.registerErrorTarget.textContent =
    "Veuillez sélectionner une agence avant de vous inscrire.";
  return;
}

    try {
      // 1) Création du compte (API)
      const registerResponse = await fetch("/api/sellers", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          lastName: form.lastName.value.trim(),
          firstName: form.firstName.value.trim(),
          email,
          password,
          phone: form.phone.value.trim(),
          address: form.address.value.trim(),
          city: form.city.value.trim(),
          postalCode: form.postalCode.value.trim(),
          country: "France",
          rgpdConsent,
          agencyId,
        }),
      });

      if (!registerResponse.ok) {
        let msg = generic;

        const ct = registerResponse.headers.get("content-type") || "";
        if (ct.includes("application/json")) {
          const err = await registerResponse.json();
          // 🔒 erreurs sensibles => rester vague
          if (err?.message === "This email is already used.") msg = generic;
          else if (err?.message) msg = err.message;
        } else {
          const errorText = await registerResponse.text();
          if (errorText && !errorText.trim().startsWith("{") && !errorText.trim().startsWith("<")) {
            msg = errorText;
          }
        }

        throw new Error(msg);
      }

      // 2) Connexion automatique JWT (utile pour tes appels API front)
      const loginResponse = await fetch("/api/login_check", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      if (loginResponse.ok) {
        const loginData = await loginResponse.json();
        localStorage.setItem("token", loginData.token);
      }
      // si ça échoue, on peut quand même tenter la session web

      // 3) Connexion automatique WEB (session Symfony) => nécessaire pour accéder aux dashboards Twig
      const body = new URLSearchParams();
      body.set("email", email);
      body.set("password", password);
      body.set("_csrf_token", csrf);

      const sessionLoginResponse = await fetch("/account", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: body.toString(),
        credentials: "same-origin",
      });

      const csrf = document.querySelector('#login-form input[name="_csrf_token"]')?.value;
      if (![200, 302, 303].includes(sessionLoginResponse.status)) {
        throw new Error("Votre compte a été créé, mais la connexion automatique a échoué. Veuillez vous connecter.");
      }

      // 302 attendu en général après login
      if (!sessionLoginResponse.ok && sessionLoginResponse.status !== 302) {
        throw new Error("Votre compte a été créé, mais la connexion automatique a échoué. Veuillez vous connecter.");
      }

      // 4) Redirection dashboard (Twig)
      window.location.replace("/seller/dashboard");
    } catch (error) {
      this.registerErrorTarget.textContent = error?.message || generic;
    }
  }
}