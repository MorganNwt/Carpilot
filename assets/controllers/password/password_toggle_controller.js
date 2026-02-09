import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['input', 'eye', 'eyeOff'];

  toggle(event) {
    const root = event.currentTarget.closest('.relative');
    const input = root.querySelector('input');

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    const eye = root.querySelector('[data-password-toggle-target="eye"]');
    const eyeOff = root.querySelector('[data-password-toggle-target="eyeOff"]');

    if (eye && eyeOff) {
      eye.classList.toggle('hidden', isHidden);
      eyeOff.classList.toggle('hidden', !isHidden);
    }
  }
}
