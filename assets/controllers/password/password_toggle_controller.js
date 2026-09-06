import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['input', 'eye', 'eyeOff'];

  toggle(event) {
    // Trouve le champ de mot de passe associé à l'icône cliquée
    const root = event.currentTarget.closest('.relative');
    const input = root.querySelector('input');

    // Bascule le type du champ entre "password" et "text"
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    // Affiche l'icône appropriée en fonction de l'état du champ
    const eye = root.querySelector('[data-password-toggle-target="eye"]');
    const eyeOff = root.querySelector('[data-password-toggle-target="eyeOff"]');

    if (eye && eyeOff) {
      eye.classList.toggle('hidden', isHidden);
      eyeOff.classList.toggle('hidden', !isHidden);
    }
  }
}
