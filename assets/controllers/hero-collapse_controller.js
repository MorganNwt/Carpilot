import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['hero', 'image', 'title'];

    connect() {
        this.startHeight = window.innerHeight;
        this.endHeight = window.innerHeight * 0.45;
        this.maxScroll = this.startHeight * 0.6;

        this.onScroll = this.onScroll.bind(this);
        window.addEventListener('scroll', this.onScroll);
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
    }

    onScroll() {
        const scrollY = Math.min(window.scrollY, this.maxScroll);
        const progress = scrollY / this.maxScroll;

        /* Hero : le bas remonte*/
        const height =
            this.startHeight -
            (this.startHeight - this.endHeight) * progress;

        this.heroTarget.style.height = `${height}px`;

        /* Image : parallax léger */
        this.imageTarget.style.transform =
            `translateY(${progress * 50}px) scale(${1.05 - progress * 0.05})`;

        /* Titre */
        const scale = 1 - progress * 0.4;
        const opacity = 1 - progress * 0.6;

        this.titleTarget.style.transform = `scale(${scale})`;
        this.titleTarget.style.opacity = Math.max(opacity, 0.6);
    }
}
