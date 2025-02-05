<template>
    <v-container fluid>
        <!-- Encabezado -->

        <v-col cols="12">
            <v-card class="mx-auto my-1 rounded-xl" elevation="10">
                <v-card-title class="primary white--text text-center justify-center">
                    Geolocation Reminder App (GERA)
                </v-card-title>
                <div class="text-center">
                    <img src="https://github.com/EmmanWayne/iconos_publicos/blob/main/imagen_gera.jpg?raw=true"
                        width="250rem" height="200rem">
                </div>


                <v-card-title class="title-style text-center justify-center">
                    ¡Hola, Bienvenid@ a GERA¡
                </v-card-title>
                <v-card-text class="text-center justify-center">
                    Tu aplicación favorita para guardar recordatorios de ubicaciones geográficas.
                    ¡Gracias por usar, GERA!
                </v-card-text>
                <v-card>
                    <v-card elevation-10>
                        <v-card-title class="title-style text-center justify-center">
                            Instrucciones:
                        </v-card-title>
                        <v-card-text class="text-center justify-center">
                            1. Guarda recordatorios de lugares o ubicaciones geográficas en las que has estado.
                        </v-card-text>
                        <v-card-text class="text-center justify-center">
                            2. Recorre y busca lugares en el mapa, selecciona el lugar en el mapa dando clic en el área.
                        </v-card-text>
                        <v-card-text class="text-center justify-center">
                            3. verifica si has estado ahí, con el botón <strong>[VERIFICAR RECORDATORIOS]</strong>.
                        </v-card-text>
                    </v-card>
                    <v-card-text class="text-center justify-center">
                        Puedes usar el mapa para conocer lugares o ubicaciones geográficas, luego
                        guarda recordatorios con esos datos.
                    </v-card-text>

                    <div class="text-center">
                        <v-card-title class="primary white--text text-center justify-center text-style">
                            <v-icon class="mr-2">mdi-map</v-icon>
                            Mapa
                        </v-card-title>
                    </div>
                    <v-card-text>
                        <div id="map" style="height: 400px;"></div>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="3">
                                    <v-text-field v-model="lat" label="Latitud" outlined dense readonly></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="3">
                                    <v-text-field v-model="lng" label="Longitud" outlined dense readonly></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="3">
                                    <v-text-field v-model="radius" label="Radio (metros)" outlined dense
                                        type="number"></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="3">
                                    <v-btn color="primary" class="text-center align-center elevation-2"
                                        style="width: 100%;" @click="checkReminders">Verificar Recordatorios</v-btn>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>


                </v-card>
            </v-card>
        </v-col>


    </v-container>
</template>


<script>

import axios from 'axios';
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss'
import "animate.css";

export default {
    data() {
        return {
            map: null,
            marker: null,
            circle: null,
            lat: 0,
            lng: 0,
            radius: 100, // Radio por defecto en metros
            reminders: [], // Lista de recordatorios desde la base de datos
        };
    },

    mounted() {
        this.initMap();
    },

    methods: {
        initMap() {
            this.map = L.map("map").setView([13.859414, -86.555829], 100); // Coordenadas de mi casa

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; OpenStreetMap contributors",
            }).addTo(this.map);

            this.map.on("click", this.setMarker);
        },
        setMarker(event) {
            const { lat, lng } = event.latlng;
            this.lat = lat.toFixed(6);
            this.lng = lng.toFixed(6);

            // Define un nuevo icono personalizado
            const customIcon = L.icon({
                iconUrl: "https://cdn-icons-png.flaticon.com/512/684/684908.png", // Reemplázalo con tu imagen de ícono
                iconSize: [32, 32], // Tamaño del icono
                iconAnchor: [16, 32], // Punto de anclaje del icono
                popupAnchor: [0, -32], // Posición del popup en relación al icono
            });

            // Elimina el marcador y el círculo anterior si existen
            if (this.marker) this.map.removeLayer(this.marker);
            if (this.circle) this.map.removeLayer(this.circle);

            // Agrega un nuevo marcador con el icono personalizado
            this.marker = L.marker([lat, lng], { icon: customIcon }).addTo(this.map);

            // Agrega un círculo de radio alrededor del marcador
            this.circle = L.circle([lat, lng], { radius: this.radius }).addTo(this.map);
        },

        async checkReminders() {

            try {
                // Obtener recordatorios desde la API
                const response = await axios.get("http://127.0.0.1:8000/api/reminders");
                this.reminders = response.data;


                const similitudeReminder = []
                // Verificar si el usuario está dentro del radio de algún recordatorio
                this.reminders.forEach(reminder => {

                    const distance = this.calculateDistance(this.lat, this.lng, reminder.latitude, reminder.longitude);

                    if (distance <= reminder.radius) {
                        similitudeReminder.push(reminder.title)

                    }
                });

                Swal.fire({
                    title: `📍 ¡Recordatorio activado! cerca de : ${similitudeReminder.join(' , ')}`,
                    icon: "success",
                    draggable: true,
                    background: "rgba(0, 0, 0, 0.8)",
                        color: "#fff",
                        showClass: {
                            popup: "animate__animated animate__fadeInRight"
                        },
                });

            

            } catch (error) {
                console.error("❌ Error al obtener los recordatorios:", error);
            }
        },

        calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371e3; // Radio de la Tierra en metros
            const φ1 = (lat1 * Math.PI) / 180;
            const φ2 = (lat2 * Math.PI) / 180;
            const Δφ = ((lat2 - lat1) * Math.PI) / 180;
            const Δλ = ((lng2 - lng1) * Math.PI) / 180;

            const a =
                Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c; // Distancia en metros
        },
        sendNotification(message) {
            if ("Notification" in window && Notification.permission === "granted") {
                new Notification(message);
            } else if ("Notification" in window && Notification.permission !== "denied") {
                Notification.requestPermission().then(permission => {
                    if (permission === "granted") {
                        new Notification(message);
                    }
                });
            } else {
                alert(message); // Fallback para navegadores sin soporte de notificaciones
            }
        },
    },
};
</script>

<style>
#map {
    border-radius: 8px;
}

.title-style {
    font-size: 1.2rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    color: black;
}

.text-style {
    font-size: 1.0rem;
    font-weight: bolder;
    display: flex;
    align-items: center;
    color: darkgreen;
}
</style>