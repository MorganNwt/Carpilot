import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["button"];

    connect() {
        document.addEventListener("scroll", this.toggleButton.bind(this));
    }

    toggleButton() {
        const docHeight = document.documentElement.scrollHeight;
        const winHeight = window.innerHeight;
        const scrollTop = window.scrollY;

        if (scrollTop + winHeight >= docHeight - 100) {
            this.buttonTarget.classList.add("show");
        } else {
            this.buttonTarget.classList.remove("show");
        }
    }
}
