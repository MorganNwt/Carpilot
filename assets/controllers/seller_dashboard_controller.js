import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    get token() {
        return localStorage.getItem('token');
    }

    get headers() {
        return {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
        };
    }

    /* ======================
     * SUPPRESSION VÉHICULE
     * ====================== */
    async deleteVehicle(event) {
        const id = event.currentTarget.dataset.id;

        if (!confirm('Supprimer ce véhicule ?')) return;

        if (!this.token) {
            window.location.href = '/account';
            return;
        }

        try {
            const response = await fetch(`/api/seller/vehicles/${id}`, {
                method: 'DELETE',
                headers: this.headers
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la suppression du véhicule');
            }

            // Rafraîchissement simple
            window.location.reload();

        } catch (error) {
            alert(error.message);
        }
    }

    /* ======================
     * SUPPRESSION COMPTE
     * ====================== */
    async deleteAccount() {
        if (!confirm('Supprimer définitivement votre compte ?')) return;

        if (!this.token) {
            window.location.href = '/account';
            return;
        }

        try {
            const response = await fetch('/api/sellers/profile', {
                method: 'DELETE',
                headers: this.headers
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la suppression du compte');
            }

            // 🔐 Nettoyage du JWT côté navigateur
            localStorage.removeItem('token');

            window.location.href = '/';

        } catch (error) {
            alert(error.message);
        }
    }
}
