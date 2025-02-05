<template>
  <v-app>
    <v-main>
      <MapaGeoReminders />
      <HomeReminders />
    </v-main>
  </v-app>
</template>

<script>
import MapaGeoReminders from "./components/MapaGeoReminders.vue";
import HomeReminders from "./components/HomeReminders.vue";

export default {
  components: { MapaGeoReminders, HomeReminders }
};
</script>
