<template>
    <v-container fluid>
        <!-- Encabezado -->
        <v-container>
            <v-card class="mx-auto my-2 rounded-xl" elevation="10">
                <v-card-title class="primary white--text text-center justify-center title-style">
                    GEOLOCATION REMINDER APP (GERA)
                </v-card-title>
                <v-img src="https://github.com/EmmanWayne/iconos_publicos/blob/main/imagen_gera.jpg?raw=true"
                    width="10rem" height="10rem" style="margin: auto;"></v-img>
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

                <v-data-table :headers="headers" :items="filteredReminders" dense class="elevation-2 table-style"
                    :footer-props="{
                        'items-per-page-text': 'Filas por página',
                        'page-text': '{0}-{1} de {2}'
                    }">
                    <template v-slot:top>
                        <v-toolbar flat color="white">
                            <v-text-field v-model="search" append-icon="mdi-magnify" label="Buscar..." single-line
                                hide-details>
                            </v-text-field>
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
        <template>
            <v-col cols="12" md="12">
                <v-card class="mx-auto my-2 rounded-xl glass-card" elevation="10">
                    <v-card-title class="primary white--text title-style">
                        <v-icon class="mr-2">mdi-map</v-icon>
                        Mapa y Simulación de Ubicación
                    </v-card-title>
                    <v-card-text>
                        <!-- Contenedor del mapa -->
                        <div id="map" class="map-container"></div>

                        <!-- Botón para obtener la ubicación real -->
                        <v-btn color="success" class="mt-3 elevation-2" @click="useCurrentLocation" block>
                            Usar Mi Ubicación
                        </v-btn>

                        <!-- Botón para simular ubicación -->
                        <v-btn color="warning" class="mt-3 elevation-2" @click="simulateLocation" block>
                            Simular Ubicación
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </template>
    </v-container>
</template>

<script>

import axios from 'axios';
import L from 'leaflet';
import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss'
import "animate.css";



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

            map: null, // Mapa Leaflet
            marker: null, // Marcador de ubicación


        };

    },

    computed: {
        filteredReminders() {
            if (!this.search) {
                return this.reminders;
            }
            const searchTerm = this.search.toLowerCase();
            return this.reminders.filter(reminder =>
                reminder.title.toLowerCase().includes(searchTerm) ||
                reminder.latitude.toString().includes(searchTerm) ||
                reminder.longitude.toString().includes(searchTerm) ||
                reminder.radius.toString().includes(searchTerm)
            );
        }
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
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El recordatorio ha sido guardado correctamente.",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutRight"
                        }
                    });
                    this.resetForm();
                })
                .catch((error) => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "¡Error!",
                        text: "El recordatorio no ha sido guardado.",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutRight"
                        }
                    });
                    console.error('Error al crear el recordatorio:', error);
                });
        },
        updateReminder(id) {
            axios
                .put(`http://localhost:8000/api/reminders/${id}`, this.reminderForm)
                .then((response) => {
                    const index = this.reminders.findIndex((reminder) => reminder.id === id);
                    this.reminders.splice(index, 1, response.data); // Actualiza el recordatorio en la lista
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El recordatorio ha sido actualizado correctamente.",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutRight"
                        }
                    });
                    this.resetForm();
                })
                .catch((error) => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "¡Error!",
                        text: "El recordatorio no ha sido actualizado.",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutRight"
                        }
                    });
                    console.error('Error al actualizar el recordatorio:', error);
                });
        },
        deleteReminder(id) {
            axios
                .delete(`http://localhost:8000/api/reminders/${id}`)
                .then(() => {
                    this.reminders = this.reminders.filter((reminder) => reminder.id !== id);
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El recordatorio ha sido eliminado correctamente.",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutRight"
                        }
                    });

                    this.resetForm(); // Elimina el recordatorio de la lista
                })
                .catch((error) => {

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "¡Error!",
                        text: "El recordatorio no ha sido eliminado.",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                        hideClass: {
                            popup: "animate__animated animate__fadeOutRight"
                        }
                    });
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

        // Inicializa el mapa centrado en un punto inicial
        initMap() {
            this.map = L.map("map").setView([19.4326, -99.1332], 13); // CDMX como valor inicial

            // Cargar mapa de OpenStreetMap
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(this.map);

            // Agregar marcador inicial
            this.marker = L.marker([19.4326, -99.1332]).addTo(this.map);
        },

        // Obtiene la ubicación real del usuario
        useCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const { latitude, longitude } = position.coords;
                    this.updateMarker(latitude, longitude);
                }, () => {
                    alert("No se pudo obtener la ubicación.");
                });
            } else {
                alert("Tu navegador no soporta la geolocalización.");
            }
        },

        // Simula una ubicación falsa
        simulateLocation() {
            const fakeLat = 40.7128; // Nueva York
            const fakeLng = -74.0060;
            this.updateMarker(fakeLat, fakeLng);
        },

        // Actualiza la ubicación del marcador en el mapa
        updateMarker(lat, lng) {
            this.marker.setLatLng([lat, lng]); // Mueve el marcador
            this.map.setView([lat, lng], 13); // Centra el mapa
        },

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
        height: 400px;
        width: 100%;
    }

}
</style>