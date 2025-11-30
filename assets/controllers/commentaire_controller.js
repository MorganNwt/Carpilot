import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["carousel", "card", "next", "prev"];

    index = 0;

    connect() {
        console.log('✅ carousel controller CONNECTED');
        console.log('cards trouvées :', this.cardTargets.length);

        this.updateCarousel();
        window.addEventListener("resize", () => this.updateCarousel());
    }

    nextSlide() {
        console.log('➡ nextSlide');
        const visible = window.innerWidth >= 1024 ? 2 : 1;

        if (this.index < this.cardTargets.length - visible) {
            this.index++;
            this.updateCarousel();
        }
    }

    prevSlide() {
        console.log('⬅ prevSlide');
        if (this.index > 0) {
            this.index--;
            this.updateCarousel();
        }
    }

    updateCarousel() {
        if (this.cardTargets.length === 0) return;

        const gap = 40; 
        const cardWidth = this.cardTargets[0].offsetWidth + gap;

        console.log('updateCarousel → index =', this.index, 'cardWidth =', cardWidth);

        this.carouselTarget.style.transform = `translateX(-${this.index * cardWidth}px)`;
    }
}
