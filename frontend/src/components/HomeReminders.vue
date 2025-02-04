<template>
    <v-container fluid>
        <!-- Encabezado -->
        <v-container>
            <v-card class="mx-auto my-2 rounded-xl" elevation="10">
                <v-card-title class="primary white--text text-center justify-center title-style">
                    GEOLOCATION REMINDER APP (GERA)
                </v-card-title>
                <v-img src="https://github.com/EmmanWayne/iconos_publicos/blob/main/imagen_gera.jpg?raw=true"
                    width="20rem" height="20rem" style="margin: auto;"></v-img>
                <v-card-text class="text--black text-center justify-center text-style my-2">
                    Para utilizar GERA, solo debes guardar recordatorios basados en
                    ubicaciones geográficas, ejemplo: Titulo: Ubicación de mi casa, Latitud: 13.8624074, Longitud:
                    -86.55593 y Radio: 815 metros, si los datos que guardas en el recordatorio están en el rango de la
                    ubicación guardada, entonces te lo notificaremos.
                    ¡Gracias por usar, GERA!
                </v-card-text>
            </v-card>
        </v-container>


        <v-col cols="12" md="12">
            <v-card class="mx-auto my-2 rounded-xl glass-card" elevation="10">
                <v-card-title class="success white--text title-style">
                    <v-icon class="mr-2">mdi-calendar-clock</v-icon>
                    Nuevo recordatorio
                </v-card-title>
                <v-card-text>
                    <v-form v-model="valid" ref="form" @submit.prevent="saveReminder">
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="4">
                                    <v-text-field v-model="reminderForm.title" label="Título" required outlined dense
                                        type="text"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2">
                                    <v-text-field v-model="reminderForm.latitude" label="Latitud" required outlined
                                        dense type="number"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2">
                                    <v-text-field v-model="reminderForm.longitude" label="Longitud" required outlined
                                        dense type="number"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2">
                                    <v-text-field v-model="reminderForm.radius" label="Radio (m)" required outlined
                                        dense type="number" min="1"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2" class="text-center">
                                    <v-btn type="submit" :disabled="!valid" color="success" class="elevation-2">
                                        <v-icon>mdi-content-save</v-icon>
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-col>




        <!-- Tabla de recordatorios -->
        <v-col cols="12" md="12">
            <v-card class="mx-auto my-2 rounded-xl glass-card" elevation="10">
                <v-card-title class="success white--text title-style">
                    <v-icon class="mr-2">mdi-calendar-clock</v-icon>
                    Lista de recordatorios
                </v-card-title>
                <v-data-table :headers="headers" :items="reminders" dense class="elevation-2 table-style" hover>
                    <template v-slot:top>
                        <v-toolbar flat color="white">
                            <v-text-field v-model="search" append-icon="mdi-magnify" label="Buscar..." single-line
                                hide-details></v-text-field>
                        </v-toolbar>
                    </template>
                    <template v-slot:item="{ item }">
                        <tr>
                            <td>{{ item.title }}</td>
                            <td>{{ item.latitude }}</td>
                            <td>{{ item.longitude }}</td>
                            <td>{{ item.radius }} m</td>
                            <td class="text-center">
                                <v-btn icon small @click="editReminder(item)">
                                    <v-icon color="blue">mdi-pencil</v-icon>
                                </v-btn>
                                <v-btn icon small @click="deleteReminder(item.id)">
                                    <v-icon color="red">mdi-delete</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-card>
        </v-col>


        <!-- Mapa y Simulación de Ubicación -->
        <v-col cols="12" md="12">
            <v-card class="mx-auto my-2 rounded-xl glass-card" elevation="10">
                <v-card-title class="primary white--text title-style">
                    <v-icon class="mr-2">mdi-map</v-icon>
                    Mapa y Simulación de Ubicación
                </v-card-title>
                <v-card-text>
                    <div id="map" class="map-container"></div>
                    <v-btn color="success" class="mt-3 elevation-2" @click="useCurrentLocation" block>
                        Usar Mi Ubicación
                    </v-btn>
                </v-card-text>
            </v-card>
        </v-col>
    </v-container>
</template>

<script>

import axios from 'axios';
import L from 'leaflet';

export default {
    data() {
        return {
            valid: false,
            reminderForm: {
                id: null,
                title: '',
                latitude: '',
                longitude: '',
                radius: '',
            },

            reminders: [],
            headers: [
                { text: 'Título', align: 'center', key: 'title' },
                { text: 'Latitud', align: 'center', key: 'latitude' },
                { text: 'Longitud', align: 'center', key: 'longitude' },
                { text: 'Radio', align: 'center', key: 'radius' },
                { text: 'Acciones', align: 'center' },
            ],

            rules: {
                required: value => !!value || 'Este campo es requerido.',
            },

            userLocation: { lat: 13.8624074, lng: -86.5523126 },
            map: null,
            userMarker: null

            
        };

    },
    mounted() {
        this.getReminders();
        this.initMap();
    },
    methods: {
        getReminders() {
            axios
                .get('http://localhost:8000/api/reminders')
                .then((response) => {
                    this.reminders = response.data;
                })
                .catch((error) => {
                    console.error('Error al obtener los recordatorios:', error);
                });
        },
        saveReminder() {
            if (this.reminderForm.id) {
                // Si existe un ID, actualizamos el recordatorio
                this.updateReminder(this.reminderForm.id);
            } else {
                // Si no existe un ID, creamos un nuevo recordatorio
                this.createReminder();
            }
        },
        createReminder() {
            axios
                .post('http://localhost:8000/api/reminders', this.reminderForm)
                .then((response) => {
                    this.reminders.push(response.data); // Añadir el nuevo recordatorio a la lista
                    this.resetForm();
                })
                .catch((error) => {
                    console.error('Error al crear el recordatorio:', error);
                });
        },
        updateReminder(id) {
            axios
                .put(`http://localhost:8000/api/reminders/${id}`, this.reminderForm)
                .then((response) => {
                    const index = this.reminders.findIndex((reminder) => reminder.id === id);
                    this.reminders.splice(index, 1, response.data); // Actualiza el recordatorio en la lista
                    this.resetForm();
                })
                .catch((error) => {
                    console.error('Error al actualizar el recordatorio:', error);
                });
        },
        deleteReminder(id) {
            axios
                .delete(`http://localhost:8000/api/reminders/${id}`)
                .then(() => {
                    this.reminders = this.reminders.filter((reminder) => reminder.id !== id);
                    () => alert('Eliminado')
                    this.resetForm(); // Elimina el recordatorio de la lista
                })
                .catch((error) => {
                    console.error('Error al eliminar el recordatorio:', error);

                });
        },
        editReminder(reminder) {
            this.reminderForm = { ...reminder }; // Rellena el formulario con los datos del recordatorio
        },
        resetForm() {
            this.reminderForm = {
                id: null,
                title: '',
                latitude: '',
                longitude: '',
                radius: '',
            };
        },

        initMap() {
            this.map = L.map('map').setView([0, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(this.map);
            this.userMarker = L.marker([0, 0]).addTo(this.map).bindPopup('Ubicación simulada');
        },

        useCurrentLocation() {
            navigator.geolocation.getCurrentPosition(
                position => {
                    this.userLocation.lat = position.coords.latitude;
                    this.userLocation.lng = position.coords.longitude;
                    this.updateUserMarker();
                },
                () => alert('No se pudo obtener la ubicación.')
            );
        },
        updateUserMarker() {
            this.userMarker.setLatLng([this.userLocation.lat, this.userLocation.lng]);
            this.map.setView([this.userLocation.lat, this.userLocation.lng], 15);
            this.checkProximity();
        },
        checkProximity() {
            this.reminders.forEach(reminder => {
                const distance = this.getDistance(
                    this.userLocation.lat, this.userLocation.lng,
                    parseFloat(reminder.latitude), parseFloat(reminder.longitude)
                );
                if (distance <= parseFloat(reminder.radius)) {
                    alert(`¡Estás cerca de "${reminder.title}"!`);
                }
            });
        },
        getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3;
            const φ1 = lat1 * (Math.PI / 180);
            const φ2 = lat2 * (Math.PI / 180);
            const Δφ = (lat2 - lat1) * (Math.PI / 180);
            const Δλ = (lon2 - lon1) * (Math.PI / 180);
            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }
    },

};

</script>

<style scoped>
/* ✅ Efecto de tarjeta translúcida estilo "Glassmorphism" */
.glass-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* ✅ Títulos estilizados */
.title-style {
    font-size: 1.3rem;
    font-weight: bolder;
    display: flex;
    align-items: center;
}

/* ✅ Tabla con bordes más suaves */
.table-style {
    border-radius: 8px;
    text-align: center;


}

/* ✅ Ajuste responsivo del mapa */
.map-container {
    width: 100%;
    height: 350px;
    border-radius: 8px;
}

.text-style {
    font-size: 1.0rem;
    font-weight: normal;
    display: flex;
    align-items: center;
}

/* ✅ Mejor ajuste en móviles */
@media (max-width: 600px) {
    .map-container {
        height: 250px;
    }

}
</style>