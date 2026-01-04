import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['profile'];

    /* ======================
     * LIFECYCLE
     * ====================== */
    connect() {
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = '/account';
            return;
        }

        this.loadProfile();

    }

    /* ======================
     * HELPERS
     * ====================== */
    get token() {
        return localStorage.getItem('token');
    }

    get headers() {
        return {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json'
        };
    }

    toggleEdit(event) {
    const container = event.currentTarget.closest('[data-id]');
    container.querySelector('[data-view]').classList.toggle('hidden');
    container.querySelector('[data-edit]').classList.toggle('hidden');
}

    /* ======================
     * PROFIL
     * ====================== */
    async loadProfile() {
        try {
            const response = await fetch('/api/sellers/profile', {
                headers: this.headers
            });

            if (!response.ok) throw new Error();

            const profile = await response.json();

            this.profileTarget.innerHTML = `
                <div class="max-w-3xl mx-auto">
                    <form class="space-y-4" data-action="submit->seller-profile#updateProfile">
                        <input type="text" name="firstName" value="${profile.firstName ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200" placeholder="Prénom" required />
                        <input type="text" name="lastName" value="${profile.lastName ?? ''}"  class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200" placeholder="Nom" required />
                        <input type="email" name="email" value="${profile.email ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"placeholder="Email" required />
                        <input type="text" name="phone" value="${profile.phone ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"placeholder="Téléphone" />
                        <input type="text" name="address" value="${profile.address ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"placeholder="Adresse" />
                        <input type="text" name="postalCode" value="${profile.postalCode ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200" placeholder="Code postal" />
                        <input type="text" name="city" value="${profile.city ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-800" placeholder="Ville" />

                        <div class="flex gap-4 pt-4 justify-center"> 
                            <button type="submit"class="bg-blue-700 text-white rounded-lg  px-8 py-4 font-semibold hover:bg-blue-600 cursor-pointer transition">
                                Mettre à jour
                            </button>
                            
                            <button type="button" data-action="click->seller-profile#deleteAccount" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 cursor-pointer transition">
                                Supprimer le compte
                            </button>
                        </div>
                    </form>
                </div>
            `;

        } catch {
            localStorage.removeItem('token');
            window.location.href = '/account';
        }
    }

    async updateProfile(event) {
        event.preventDefault();

        const form = event.currentTarget;

        const payload = {
            firstName: form.firstName.value,
            lastName: form.lastName.value,
            email: form.email.value,
            phone: form.phone.value,
            address: form.address.value,
            postalCode: form.postalCode.value,
            city: form.city.value
        };

        const response = await fetch('/api/sellers/profile', {
            method: 'PUT',
            headers: this.headers,
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            toastr.error('Erreur lors de la mise à jour du profil');
            return;
        }

        toastr.success('Profil mis à jour avec succès');
    }

    async deleteAccount() {
        if (!confirm('Supprimer définitivement votre compte ?')) return;

        const response = await fetch('/api/sellers/profile', {
            method: 'DELETE',
            headers: this.headers
        });

        if (response.ok) {
            localStorage.removeItem('token');
            window.location.href = '/';
        }
    }
}
