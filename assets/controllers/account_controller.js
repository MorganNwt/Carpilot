import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = [
    'loginEmail',
    'loginPassword',
    'loginError',
    'registerForm',
    'registerError'
  ];

    /* ======================
    * CONNEXION
    * ====================== */
  async login(event) {
    event.preventDefault();
    this.loginErrorTarget.textContent = '';

    try {
      const response = await fetch('/api/login_check', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email: this.loginEmailTarget.value,
          password: this.loginPasswordTarget.value
        })
      });

      if (!response.ok) {
        throw new Error('Identifiants incorrects');
      }

      const data = await response.json();

      if (!data.token) {
        throw new Error('Token manquant dans la réponse');
      }

      //  Stockage du JWT
      localStorage.setItem('token', data.token);

      //  PRIORITÉ : si on vient du flow "plaque" → retour vers le formulaire pré-rempli
      const redirect = sessionStorage.getItem('after_login_redirect');
      if (redirect) {
        sessionStorage.removeItem('after_login_redirect');
        window.location.href = redirect;
        return;
      }

      //  Décodage du token pour connaître le rôle
      const decoded = this.decodeJwt(data.token);
      const roles = decoded.roles ?? [];

      console.log('JWT décodé:', decoded);
      console.log('Rôles détectés:', roles);


      //  Redirection par rôle
      if (roles.includes('ROLE_ADMIN')) {
        window.location.href = '/admin/dashboard';
        return;
      }

      if (roles.includes('ROLE_AGENT')) {
        window.location.href = '/agent/dashboard';
        return;
      }

      if (roles.includes('ROLE_SELLER')) {
        window.location.href = '/seller/dashboard';
        return;
      }

      // fallback sécurité
      window.location.href = '/';

    } catch (error) {
      console.error(error);
      this.loginErrorTarget.textContent = error.message;
    }
  }

  /* ======================
   * INSCRIPTION
   * ====================== */
  async register(event) {
    event.preventDefault();
    this.registerErrorTarget.textContent = '';

    const form = this.registerFormTarget;

    const password = form.querySelector('[name="password"]').value;
    const passwordConfirm = form.querySelector('[name="passwordConfirm"]').value;

    if (password !== passwordConfirm) {
      this.registerErrorTarget.textContent =
        'Les mots de passe ne correspondent pas';
      return;
    }

  
    const rgpdConsent = form.querySelector('[name="rgpdConsent"]').checked;

    if (!rgpdConsent) {
      this.registerErrorTarget.textContent = 'Vous devez accepter la politique de confidentialité (RGPD).';
      return;
    }

    try {
      const response = await fetch('/api/sellers', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          lastName: form.lastName.value,
          firstName: form.firstName.value,
          email: form.email.value,
          password: password,
          phone: form.phone.value,
          address: form.address.value,
          city: form.city.value,
          postalCode: form.postalCode.value,
          country: 'France',
          rgpdConsent: form.querySelector('[name="rgpdConsent"]').checked
        })
      });

      if (!response.ok) {
        throw new Error('Erreur lors de la création du compte');
      }

      alert('Compte créé avec succès. Vous pouvez vous connecter.');
      form.reset();

    } catch (error) {
      this.registerErrorTarget.textContent = error.message;
    }
  }

  /* ======================
   * UTILS
   * ====================== */
  decodeJwt(token) {
    try {
      const payload = token.split('.')[1];

      // Support Base64URL (JWT) -> Base64
      const base64 = payload.replace(/-/g, '+').replace(/_/g, '/');
      const padded = base64.padEnd(base64.length + (4 - (base64.length % 4)) % 4, '=');

      return JSON.parse(atob(padded));
    } catch (e) {
      return {};
    }
  }
}
