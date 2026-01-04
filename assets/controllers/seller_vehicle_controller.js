import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['vehicles'];

    /* ======================
     * LIFECYCLE
     * ====================== */
    connect() {
        const token = localStorage.getItem('token');

        if (!token) {
            window.location.href = '/account';
            return;
        }

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

    toggleEdit(event) {
        const container = event.currentTarget.closest('[data-id]');
        const view = container.querySelector('[data-view]');
        const edit = container.querySelector('[data-edit]');

        const isEditing = !edit.classList.contains('hidden');

        view.classList.toggle('hidden');
        edit.classList.toggle('hidden');

        if (isEditing) {
            edit.reset();
        }
    }

    formatDate(dateString) {
        if (!dateString) return '—';

        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    async loadVehicles() {
        try {
            const response = await fetch('/api/seller/vehicles', {
                headers: this.headers
            });

            if (!response.ok) throw new Error();

            const vehicles = await response.json();

            if (!vehicles.length) {
                this.vehiclesTarget.innerHTML =
                    `<p class="text-gray-400">Aucun véhicule enregistré</p>`;
                return;
            }

            this.vehiclesTarget.innerHTML = vehicles.map(vehicle => `
                <div class="border-b py-6" data-id="${vehicle.id}">
                    
                    <!-- ================= LECTURE ================= -->
                    <div class="flex flex-col gap-4" data-view>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-lg font-semibold">${vehicle.plate}</p>
                                <p class="text-gray-600">
                                    ${vehicle.brand} ${vehicle.model}
                                </p>
                                <p class="text-gray-400 text-sm">
                                    Ajouté le ${this.formatDate(vehicle.createdAt)}
                                </p>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="bg-blue-700 text-white px-4 py-2 rounded-lg
                                        hover:bg-blue-600 transition cursor-pointer"
                                    data-action="click->seller-vehicle#toggleEdit">
                                    Modifier
                                </button>

                                <button
                                type="button"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg
                                    hover:bg-red-700 transition cursor-pointer"
                                data-action="click->seller-vehicle#deleteVehicle">
                                Supprimer
                            </button>       
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="badge">${vehicle.energy}</span>
                            <span class="badge">${vehicle.gearBox}</span>
                            <span class="badge">${vehicle.horsePower} ch</span>
                            <span class="badge">${vehicle.doors} portes</span>
                            <span class="badge">${vehicle.seats} places</span>
                            <span class="badge">${vehicle.bodyType}</span>
                            <span class="badge">Couleur : ${vehicle.color}</span>
                        </div>

                        <p class="text-sm">
                            Estimation :
                            <span class="${vehicle.estimation ? 'text-green-600' : 'text-gray-400'} font-semibold">
                                ${vehicle.estimation?.amount ? vehicle.estimation.amount + ' €' : 'Aucune estimation'}
                            </span>
                        </p>
                    </div>

                    <!-- ================= ÉDITION ================= -->
                    <form class="hidden mt-6 grid grid-cols-2 md:grid-cols-4 gap-4" data-edit data-action="submit->seller-vehicle#updateVehicle" data-id="${vehicle.id}">

                        <input name="plate" value="${vehicle.plate ?? ''}" class="input" placeholder="Plaque"/>
                        <input name="vin" value="${vehicle.vin ?? ''}" class="input" placeholder="VIN"/>
                        <input name="brand" value="${vehicle.brand ?? ''}" class="input" placeholder="Marque"/>
                        <input name="model" value="${vehicle.model ?? ''}" class="input" placeholder="Modèle"/>
                        <input name="version" value="${vehicle.version ?? ''}" class="input" placeholder="Version"/>
                        <input name="energy" value="${vehicle.energy ?? ''}" class="input" placeholder="Énergie"/>
                        <input name="horsePower" value="${vehicle.horsePower ?? ''}" class="input" placeholder="Puissance (ch)"/>
                        <input name="fiscalPower" value="${vehicle.fiscalPower ?? ''}" class="input" placeholder="Puissance fiscale"/>
                        <input name="gearBox" value="${vehicle.gearBox ?? ''}" class="input" placeholder="Boîte de vitesse"/>
                        <input name="doors" value="${vehicle.doors ?? ''}" class="input" placeholder="Nombre de portes"/>
                        <input name="seats" value="${vehicle.seats ?? ''}" class="input" placeholder="Nombre de places"/>
                        <input name="bodyType" value="${vehicle.bodyType ?? ''}" class="input" placeholder="Type de carrosserie"/>
                        <input name="color" value="${vehicle.color ?? ''}" class="input" placeholder="Couleur"/>
                        <input name="weightKg" value="${vehicle.weightKg ?? ''}" class="input" placeholder="Poids (kg)"/>

                        <div class="col-span-full flex gap-3 justify-end mt-4">
                            <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition cursor-pointer">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
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

    // récupère les champs du form
    const raw = Object.fromEntries(new FormData(form));

    // normalise : "" -> null + cast int/float
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
        await this.loadVehicles();

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