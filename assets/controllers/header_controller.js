import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["scrollLink", "button", "menu"];

    connect() {
        this.initSmoothScroll();
        this.initMenuAgences();
    }

    initSmoothScroll() {
        this.scrollLinkTargets.forEach(link => {
            link.addEventListener("click", (e) => {
                const target = link.getAttribute("href");
                if (target.length > 1 && document.querySelector(target)) {
                    e.preventDefault();
                    document.querySelector(target).scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            });
        });
    }

    initMenuAgences() {
        if (!this.hasButtonTarget || !this.hasMenuTarget) return;

        this.buttonTarget.addEventListener("click", () => {
            this.menuTarget.classList.toggle("hidden");
        });

        document.addEventListener("click", (event) => {
            if (!this.buttonTarget.contains(event.target) && !this.menuTarget.contains(event.target)) {
                this.menuTarget.classList.add("hidden");
            }
        });
    }
}
