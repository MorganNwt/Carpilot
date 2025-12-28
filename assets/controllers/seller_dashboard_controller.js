import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['profile', 'vehicles'];

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
        this.loadVehicles();
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
                    <form class="space-y-4" data-action="submit->seller-dashboard#updateProfile">
                        <input type="text" name="firstName" value="${profile.firstName ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200" placeholder="Prénom" required />
                        <input type="text" name="lastName" value="${profile.lastName ?? ''}"  class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200" placeholder="Nom" required />
                        <input type="email" name="email" value="${profile.email ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"placeholder="Email" required />
                        <input type="text" name="phone" value="${profile.phone ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"placeholder="Téléphone" />
                        <input type="text" name="address" value="${profile.address ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"placeholder="Adresse" />
                        <input type="text" name="postalCode" value="${profile.postalCode ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200" placeholder="Code postal" />
                        <input type="text" name="city" value="${profile.city ?? ''}"class="input w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-800" placeholder="Ville" />

                        <div class="flex gap-4 pt-4 justify-center"> 
                            <button type="submit"class="bg-blue-900 text-white rounded-lg  px-8 py-4 font-semibold hover:bg-blue-700 cursor-pointer transition">
                                Mettre à jour
                            </button>
                            
                            <button type="button" data-action="click->seller-dashboard#deleteAccount" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 cursor-pointer transition">
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

   /* ======================
    * VÉHICULES
    * ====================== */
    async loadVehicles() {
        try {
            const response = await fetch('/api/seller/vehicles', {
                headers: this.headers
            });

            if (!response.ok) throw new Error();

            const vehicles = await response.json();

            if (vehicles.length === 0) {
                this.vehiclesTarget.innerHTML =
                    `<p class="text-gray-500">Aucun véhicule enregistré</p>`;
                return;
            }

            this.vehiclesTarget.innerHTML = vehicles.map(vehicle => `
                <form class="border-b py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4" data-id="${vehicle.id}" data-action="submit->seller-dashboard#updateVehicle">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                        <input name="brand" value="${vehicle.brand ?? ''}" class="border rounded px-3 py-1 w-32 focus:ring focus:ring-blue-200" placeholder="Marque"/>
                        <input name="model" value="${vehicle.model ?? ''}" class="border rounded px-3 py-1 w-32 focus:ring focus:ring-blue-200" placeholder="Modèle"/>
                        <input name="plate" value="${vehicle.plate ?? ''}" class="border rounded px-3 py-1 w-28 focus:ring focus:ring-blue-200" placeholder="Plaque"/>
                        <input name="energy" value="${vehicle.energy ?? ''}" class="border rounded px-3 py-1 w-24 focus:ring focus:ring-blue-200" placeholder="Énergie"/>
                        <input name="horsePower" value="${vehicle.horsePower ?? ''}" class="border rounded px-3 py-1 w-20 focus:ring focus:ring-blue-200" placeholder="ch"/>

                        <span class="${vehicle.estimation ? 'text-green-600' : 'text-gray-400'}">
                            ${vehicle.estimation?.amount ? vehicle.estimation + ' €' : 'Aucune estimation'}
                        </span>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg hover:bg-blue-600 cursor-pointer transition">
                            Modifier
                        </button>

                        <button
                            type="button"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg
                                hover:bg-red-700 cursor-pointer transition"
                            data-id="${vehicle.id}"
                            data-action="click->seller-dashboard#deleteVehicle"
                        >
                            Supprimer
                        </button>
                    </div>
                </form>
            `).join('');
        } catch {
            localStorage.removeItem('token');
            window.location.href = '/account';
        }
    }

    async updateVehicle(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const id = form.dataset.id;

    // 1) récupère les champs du form
    const raw = Object.fromEntries(new FormData(form));

    // 2) normalise : "" -> null + cast int/float
    const payload = {
        plate: raw.plate?.trim() || null,
        vin: raw.vin?.trim() || null,
        brand: raw.brand?.trim() || null,
        model: raw.model?.trim() || null,
        version: raw.version?.trim() || null,
        energy: raw.energy?.trim() || null,
        gearBox: raw.gearBox?.trim() || null,
        bodyType: raw.bodyType?.trim() || null,
        color: raw.color?.trim() || null,
        registrationDate: raw.registrationDate?.trim() || null,

        // numbers
        horsePower: raw.horsePower?.trim() ? Number.parseInt(raw.horsePower, 10) : null,
        fiscalPower: raw.fiscalPower?.trim() ? Number.parseFloat(raw.fiscalPower) : null,
        doors: raw.doors?.trim() ? Number.parseInt(raw.doors, 10) : null,
        seats: raw.seats?.trim() ? Number.parseInt(raw.seats, 10) : null,
        weightKg: raw.weightKg?.trim() ? Number.parseInt(raw.weightKg, 10) : null,
    };

    try {
        const response = await fetch(`/api/seller/vehicles/update/${id}`, {
            method: 'PUT',
            headers: this.headers,
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            // 3) affiche les erreurs de validation renvoyées par l’API
            const err = await response.json().catch(() => null);

            // format Symfony validator souvent: { violations: [{propertyPath, title/message}] }
            if (err?.violations?.length) {
                toastr.error(err.violations.map(v => `${v.propertyPath} : ${v.message}`).join('<br/>'));
            } else {
                toastr.error('Erreur lors de la mise à jour du véhicule');
            }
            return;
        }

        toastr.success('Véhicule mis à jour avec succès');

    } catch (e) {
        toastr.error('Erreur réseau');
    }
}

    async deleteVehicle(event) {
        const id = event.currentTarget.dataset.id;

        if (!confirm('Supprimer ce véhicule ?')) return;

        try {
            await fetch(`/api/seller/vehicles/${id}`, {
                method: 'DELETE',
                headers: this.headers
            });

            toastr.success('Véhicule supprimé');
            this.loadVehicles();

        } catch {
            toastr.error('Erreur lors de la suppression du véhicule');
        }
    }

}
