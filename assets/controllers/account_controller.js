import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["registerForm", "registerError"];

  connect() {
    const url = new URL(window.location.href);

    // On regarde s'il y a un redirect dans la query (ex: /account?redirect=/seller/estimation/result) 
    // ou dans le storage (ex: après un click "commencer l'estimation" depuis la home)
    const fromQuery = url.searchParams.get("redirect") || "";
    
    const fromStorage = sessionStorage.getItem("after_login_redirect") || "";

    // Priorité au flux (storage) puis query
    const redirect = this.safeRedirect(fromStorage || fromQuery);

    // Injecter dans le formulaire d'inscription (si présent sur la page)
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

    // Injecter dans login (si le formulaire de login est sur la même page, ex: /account)
    const loginRedirectInput = document.querySelector('form[action="/account"] input[name="redirect"]');
    if (loginRedirectInput) {
      loginRedirectInput.value = redirect;
    }
  }

  // Evénement à la soumission du formulaire d'inscription
  async register(event) {
    event.preventDefault();
    if (this.hasRegisterErrorTarget) this.registerErrorTarget.textContent = "";

    const form = this.registerFormTarget;

    // Validations basiques côté client (en plus des validations côté serveur)
    const validators = [
      { field: "lastName", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(v), msg: "Nom invalide." },
      { field: "firstName", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(v), msg: "Prénom invalide." },
      { field: "email", ok: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v), msg: "Email invalide." },
      { field: "phone", ok: (v) => /^(?:\+33|0)[1-9](?:[ .-]?\d{2}){4}$/.test(v), msg: "Téléphone invalide." },
      { field: "postalCode", ok: (v) => /^\d{5}$/.test(v), msg: "Code postal invalide." },
      { field: "city", ok: (v) => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,100}$/.test(v), msg: "Ville invalide." },
      { field: "address", ok: (v) => String(v).trim().length >= 5, msg: "Adresse trop courte." },
    ];

    // On boucle sur les règles de validation et on affiche la première erreur rencontrée
    for (const r of validators) {
      const value = String(form[r.field]?.value ?? "").trim();
      if (!r.ok(value)) return this.fail(r.msg);
    }

    // Validations spécifiques du mot de passe
    const password = form.querySelector('[name="password"]').value;
    const passwordConfirm = form.querySelector('[name="passwordConfirm"]').value;

    if (password !== passwordConfirm) return this.fail("Les mots de passe ne correspondent pas");

    const specialsCount = (password.match(/[^a-zA-Z0-9]/g) || []).length;
    if (password.length < 13 || !/[A-Z]/.test(password) || !/[0-9]/.test(password) || specialsCount < 2) {
      return this.fail("Mot de passe invalide : 13 caractères, 1 majuscule, 1 chiffre et 2 caractères spéciaux requis.");
    }

    // rgpdConsent et agencyId sont validés dans le backend
    const rgpdConsent = form.querySelector('[name="rgpdConsent"]').checked;
    if (!rgpdConsent) return this.fail("Vous devez accepter la politique de confidentialité (RGPD).");

    const agencyMeta = document.querySelector('meta[name="selected-agency-id"]');
    const agencyId = Number(agencyMeta?.content || 0);
    if (!agencyId) return this.fail("Veuillez sélectionner une agence avant de vous inscrire.");

    // redirect injecté au connect
    const redirectRaw = form.querySelector('input[name="redirect"]')?.value || "";
    const redirect = this.safeRedirect(redirectRaw);

    const generic =
      "Une erreur s’est produite pendant l’inscription. Veuillez recommencer. Nous nous excusons pour la gêne occasionnée.";

    const email = form.email.value.trim();

    try {
      //  REGISTER API (qui crée le compte et vérifie que tout est ok, ex: RGPD, agence, etc)
      const registerResponse = await fetch("/api/sellers", {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify({
          lastName: form.lastName.value.trim(),
          firstName: form.firstName.value.trim(),
          email,
          password,
          phone: form.phone.value.trim(),
          address: form.address.value.trim(),
          city: form.city.value.trim(),
          postalCode: form.postalCode.value.trim(),
          country: form.country?.value.trim() || "France",
          rgpdConsent,
          agencyId,
        }),
      });

      // Gestion des erreurs d'inscription avec parsing intelligent du message d'erreur
      if (!registerResponse.ok) {
        let msg = generic;
        const ct = registerResponse.headers.get("content-type") || "";

        if (ct.includes("application/json")) {
          const err = await registerResponse.json().catch(() => null);
          if (err?.message === "This email is already used.") msg = generic;
          else if (err?.message) msg = err.message;
        } else {
          const errorText = await registerResponse.text().catch(() => "");
          if (errorText && !errorText.trim().startsWith("{") && !errorText.trim().startsWith("<")) msg = errorText;
        }

        throw new Error(msg);
      }

      // LOGIN API (JWT) pour récupérer le token et le stocker en local (qui sera utilisé pour les appels API protégés, ex: pré-remplissage par plaque)
      const loginResponse = await fetch("/api/login_check", {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      if (loginResponse.ok) {
        const loginData = await loginResponse.json().catch(() => null);
        if (loginData?.token) localStorage.setItem("token", loginData.token);
      }

      // Redirection vers la page souhaitée (ex: estimation pré-remplie) après inscription réussie
      const csrf = document.querySelector('form[action="/account"] input[name="_csrf_token"]')?.value || "";

      const postLogin = document.createElement("form");
      postLogin.method = "POST";
      postLogin.action = "/account";

      postLogin.appendChild(this.hidden("email", email));
      postLogin.appendChild(this.hidden("password", password));
      if (redirect) postLogin.appendChild(this.hidden("redirect", redirect));
      if (csrf) postLogin.appendChild(this.hidden("_csrf_token", csrf));

      document.body.appendChild(postLogin);

      // On ne supprime le storage QUE si c'est lui qu'on a consommé
      // (sinon, on risque de casser un autre flux qui l'utilise encore)
      const stored = sessionStorage.getItem("after_login_redirect");
      if (stored && this.safeRedirect(stored) === redirect) {
        sessionStorage.removeItem("after_login_redirect");
      }

      postLogin.submit();
    } catch (error) {
      this.fail(error?.message || generic);
    }
  }

  // On autorise uniquement les chemins internes
  safeRedirect(value) {
    const v = String(value || "").trim();
    if (!v) return "";
    if (v.startsWith("/") && !v.startsWith("//")) return v;
    return "";
  }

  // Crée un input hidden pour le formulaire de post-login
  hidden(name, value) {
    const i = document.createElement("input");
    i.type = "hidden";
    i.name = name;
    i.value = value ?? "";
    return i;
  }

  fail(message) {
    if (this.hasRegisterErrorTarget) this.registerErrorTarget.textContent = message;
  }
}