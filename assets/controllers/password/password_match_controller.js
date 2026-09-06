import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['password', 'confirm', 'message'];

  check() {
    // Récupère les valeurs des champs de mot de passe et de confirmation
    const pwd = this.passwordTarget.value || '';
    const confirm = this.confirmTarget.value || '';

    // Tant que l'utilisateur n'a pas commencé la confirmation, on garde rouge mais discret
    if (confirm.length === 0) {
      this.messageTarget.textContent = '* Les mots de passe doivent correspondre';
      this.messageTarget.classList.add('text-red-500');
      this.messageTarget.classList.remove('text-green-600');
      return;
    }

    // Si les deux champs sont remplis, on vérifie la correspondance
    if (pwd === confirm) {
      this.messageTarget.textContent = '* Les mots de passe correspondent';
      this.messageTarget.classList.remove('text-red-500');
      this.messageTarget.classList.add('text-green-600');
    } else {
      this.messageTarget.textContent = '* Les mots de passe doivent correspondre';
      this.messageTarget.classList.add('text-red-500');
      this.messageTarget.classList.remove('text-green-600');
    }
  }
}
