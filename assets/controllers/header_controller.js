import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        "scrollLink",
        "button",
        "menu",
        "mobileMenu",
        "mobileAgencyMenu"
    ];

    connect() {
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        document.addEventListener("click", this.handleOutsideClick);
    }

    disconnect() {
        document.removeEventListener("click", this.handleOutsideClick);
        document.body.classList.remove("overflow-hidden");
    }

    scrollToSection(event) {
        const link = event.currentTarget;
        const target = link.getAttribute("href");

        if (target && target.length > 1) {
            const element = document.querySelector(target);

            if (element) {
                event.preventDefault();
                element.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

                if (this.hasMobileMenuTarget) {
                    this.closeMobileMenu();
                }
            }
        }
    }

    toggleAgencyMenu(event) {
        event.stopPropagation();

        if (!this.hasMenuTarget) return;
        this.menuTarget.classList.toggle("hidden");
    }

    closeAgencyMenu() {
        if (!this.hasMenuTarget) return;
        this.menuTarget.classList.add("hidden");
    }

    openMobileMenu() {
        if (!this.hasMobileMenuTarget) return;
        this.mobileMenuTarget.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
    }

    closeMobileMenu() {
        if (!this.hasMobileMenuTarget) return;
        this.mobileMenuTarget.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");

        if (this.hasMobileAgencyMenuTarget) {
            this.mobileAgencyMenuTarget.classList.add("hidden");
        }
    }

    toggleMobileAgencyMenu() {
        if (!this.hasMobileAgencyMenuTarget) return;
        this.mobileAgencyMenuTarget.classList.toggle("hidden");
    }

    handleOutsideClick(event) {
        if (!this.hasButtonTarget || !this.hasMenuTarget) return;

        const clickedInsideButton = this.buttonTarget.contains(event.target);
        const clickedInsideMenu = this.menuTarget.contains(event.target);

        if (!clickedInsideButton && !clickedInsideMenu) {
            this.closeAgencyMenu();
        }
    }
}