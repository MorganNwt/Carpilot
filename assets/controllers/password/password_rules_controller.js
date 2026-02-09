import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['password', 'len', 'upper', 'digit', 'special'];

  check() {
    const value = this.passwordTarget.value ?? '';

    const okLen = value.length >= 13;
    const okUpper = /[A-Z]/.test(value);
    const okDigit = /[0-9]/.test(value);

    // "spéciaux" = tout ce qui n'est ni lettre ni chiffre
    const specialsCount = (value.match(/[^a-zA-Z0-9]/g) || []).length;
    const okSpecial = specialsCount >= 2;

    this.setStatus(this.lenTarget, okLen);
    this.setStatus(this.upperTarget, okUpper);
    this.setStatus(this.digitTarget, okDigit);
    this.setStatus(this.specialTarget, okSpecial);
  }

  setStatus(el, ok) {
    el.classList.toggle('text-green-600', ok);
    el.classList.toggle('text-red-500', !ok);
  }
}
