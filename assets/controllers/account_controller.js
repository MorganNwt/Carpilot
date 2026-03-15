import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["registerForm", "registerError"];

  connect() {
    const url = new URL(window.location.href);

    // Priorité au redirect stocké par un flux métier, sinon query string
    const fromQuery = url.searchParams.get("redirect") || "";
    const fromStorage = sessionStorage.getItem("after_login_redirect") || "";
    const redirect = this.safeRedirect(fromStorage || fromQuery);

    // Injecter redirect dans le formulaire d'inscription
    if (this.hasRegisterFormTarget) {
      let input = this.registerFormTarget.querySelector('input[name="redirect"]');

      if (!input) {
        input = document.createElement("input");
        input.type = "hidden";
        input.name = "redirect";
        this.registerFormTarget.appendChild(input);
      }

      input.value = redirect;
    }

    // Injecter redirect dans le formulaire de login Symfony sur /account
    const loginRedirectInput = document.querySelector('form[action="/account"] input[name="redirect"]');
    if (loginRedirectInput) {
      loginRedirectInput.value = redirect;
    }
  }

  async register(event) {
    event.preventDefault();

    if (this.hasRegisterErrorTarget) {
      this.registerErrorTarget.textContent = "";
    }

    const form = this.registerFormTarget;

    const validators = [
      { field: "lastName", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(v), msg: "Nom invalide." },
      { field: "firstName", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(v), msg: "Prénom invalide." },
      { field: "email", ok: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v), msg: "Email invalide." },
      { field: "phone", ok: (v) => /^(?:\+33|0)[1-9](?:[ .-]?\d{2}){4}$/.test(v), msg: "Téléphone invalide." },
      { field: "postalCode", ok: (v) => /^\d{5}$/.test(v), msg: "Code postal invalide." },
      { field: "city", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,100}$/.test(v), msg: "Ville invalide." },
      { field: "address", ok: (v) => String(v).trim().length >= 5, msg: "Adresse trop courte." },
    ];

    for (const rule of validators) {
      const value = String(form[rule.field]?.value ?? "").trim();
      if (!rule.ok(value)) {
        return this.fail(rule.msg);
      }
    }

    const password = form.querySelector('[name="password"]').value;
    const passwordConfirm = form.querySelector('[name="passwordConfirm"]').value;

    if (password !== passwordConfirm) {
      return this.fail("Les mots de passe ne correspondent pas.");
    }

    const specialsCount = (password.match(/[^a-zA-Z0-9]/g) || []).length;
    if (
      password.length < 13 ||
      !/[A-Z]/.test(password) ||
      !/[0-9]/.test(password) ||
      specialsCount < 2
    ) {
      return this.fail("Mot de passe invalide : 13 caractères, 1 majuscule, 1 chiffre et 2 caractères spéciaux requis.");
    }

    const rgpdConsent = form.querySelector('[name="rgpdConsent"]').checked;
    if (!rgpdConsent) {
      return this.fail("Vous devez accepter la politique de confidentialité (RGPD).");
    }

    const agencyMeta = document.querySelector('meta[name="selected-agency-id"]');
    const agencyId = Number(agencyMeta?.content || 0);
    if (!agencyId) {
      return this.fail("Veuillez sélectionner une agence avant de vous inscrire.");
    }

    const redirectRaw = form.querySelector('input[name="redirect"]')?.value || "";
    const redirect = this.safeRedirect(redirectRaw);

    const genericError =
      "Une erreur s’est produite pendant l’inscription. Veuillez recommencer. Nous nous excusons pour la gêne occasionnée.";

    const email = form.email.value.trim();

    try {
      // Nettoyage préventif d'un ancien token
      localStorage.removeItem("token");

      // 1) Création du compte seller
      const registerResponse = await fetch("/api/sellers", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
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
          country: form.country?.value.trim(),
          rgpdConsent,
          agencyId,
        }),
      });

      if (!registerResponse.ok) {
        let message = genericError;
        const contentType = registerResponse.headers.get("content-type") || "";

        if (contentType.includes("application/json")) {
          const err = await registerResponse.json().catch(() => null);
          if (err?.message === "This email is already used.") {
            message = genericError;
          } else if (err?.message) {
            message = err.message;
          }
        } else {
          const text = await registerResponse.text().catch(() => "");
          if (text && !text.trim().startsWith("{") && !text.trim().startsWith("<")) {
            message = text;
          }
        }

        throw new Error(message);
      }

      // 2) Login API pour récupérer le JWT
      const loginApiResponse = await fetch("/api/login_check", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      if (!loginApiResponse.ok) {
        throw new Error("Compte créé, mais la connexion API automatique a échoué.");
      }

      const loginData = await loginApiResponse.json().catch(() => null);
      if (!loginData?.token) {
        throw new Error("Compte créé, mais aucun token JWT n’a été retourné.");
      }

      // Stockage unique du JWT côté front
      localStorage.setItem("token", loginData.token);

      // 3) Login Symfony web pour créer la session côté site
      const csrf = document.querySelector('form[action="/account"] input[name="_csrf_token"]')?.value || "";

      const postLogin = document.createElement("form");
      postLogin.method = "POST";
      postLogin.action = "/account";

      postLogin.appendChild(this.hidden("email", email));
      postLogin.appendChild(this.hidden("password", password));

      if (redirect) {
        postLogin.appendChild(this.hidden("redirect", redirect));
      }

      if (csrf) {
        postLogin.appendChild(this.hidden("_csrf_token", csrf));
      }

      document.body.appendChild(postLogin);

      const storedRedirect = sessionStorage.getItem("after_login_redirect");
      if (storedRedirect && this.safeRedirect(storedRedirect) === redirect) {
        sessionStorage.removeItem("after_login_redirect");
      }

      postLogin.submit();
    } catch (error) {
      this.fail(error?.message || genericError);
    }
  }

  safeRedirect(value) {
    const v = String(value || "").trim();
    if (!v) return "";
    if (v.startsWith("/") && !v.startsWith("//")) return v;
    return "";
  }

  hidden(name, value) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value ?? "";
    return input;
  }

  fail(message) {
    if (this.hasRegisterErrorTarget) {
      this.registerErrorTarget.textContent = message;
    }
  }
}