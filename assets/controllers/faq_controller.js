import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['content', 'icon'];

    toggle(event) {
        const button = event.currentTarget;
        const content = button.nextElementSibling;
        const icon = button.querySelector('svg');

        // Si ouvert → fermer
        if (!content.classList.contains('hidden')) {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
            return;
        }

        // Tout fermer
        this.contentTargets.forEach(c => c.classList.add('hidden'));
        this.iconTargets.forEach(i => i.classList.remove('rotate-180'));

        // Ouvrir celui cliqué
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    }
}
