<template>
    <v-container fluid>

        <!-- Formulario de recordatorios -->
        <v-col cols="12" md="12">
            <v-card class="mx-auto  rounded-xl" elevation="10">
                <v-card-title class="primary white--text text-center justify-center text-style">
                    <v-icon class="mr-2">mdi-calendar-clock</v-icon>
                    Recordatorios
                </v-card-title>
                <v-card-text>
                    <v-card-title class="title-style">
                        Nuevo recordatorio:
                    </v-card-title>
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
                                    <v-btn type="submit" :disabled="!valid" color="success" style="width: 100%;"
                                        class="elevation-2">
                                        <v-icon>mdi-content-save</v-icon>
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>
                    <v-card-title class="title-style">
                        Lista de recordatorios:
                    </v-card-title>
                    <!-- Tabla de recordatorios -->
                    <v-col cols="12" md="12">
                        <v-card class="mx-auto my-2 rounded-xl" elevation="10">


                            <v-data-table :headers="headers" :items="filteredReminders" dense
                                class="elevation-2 table-style" :footer-props="{
                                    'items-per-page-text': 'Filas por página',
                                    'page-text': '{0}-{1} de {2}'
                                }">
                                <template v-slot:top>
                                    <v-toolbar flat color="white">
                                        <v-text-field v-model="search" append-icon="mdi-magnify" label="Buscar..."
                                            single-line hide-details>
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
                </v-card-text>
            </v-card>
        </v-col>




    </v-container>
</template>

<script>

import axios from 'axios';
import Swal from 'sweetalert2/dist/sweetalert2.js'
import 'sweetalert2/src/sweetalert2.scss'
import "animate.css";


export default {
    data() {
        return {
            search: '',
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
                { text: 'Acciones', align: 'center', key: 'acciones' },
            ],

            rules: {
                required: value => !!value || 'Este campo es requerido.',
            },

        };

    },

    computed: {
        filteredReminders() {
            if (!this.search) return this.reminders;
            return this.reminders.filter(reminder =>
                reminder.title.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    },


    mounted() {
        this.getReminders();
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
                    this.resetForm();
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
                    this.resetForm();
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
                    this.resetForm();
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
    font-size: 1.2rem;
    font-weight: bolder;
    display: flex;
    align-items: center;
    color: black;
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
    font-size: 1.1rem;
    font-weight: bolder;
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