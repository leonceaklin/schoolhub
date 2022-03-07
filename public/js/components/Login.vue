<template>
  <v-form @submit.prevent="fetchData">
  <v-card-title>
    <v-btn icon class="mr-2" @click="subjectsDialog = false"><v-icon>mdi-close</v-icon></v-btn>
    SAL-Login
  </v-card-title>
   <v-divider></v-divider>
  <v-card-text>
    <p class="mb-6">
    Logge dich mit deinen SAL-Zugangsdaten ein, um alle Funktionen zu nutzen.</p>
    <v-autocomplete outlined :items="schools" item-text="name" item-value="identifier" label="Schule" v-model="school"></v-autocomplete>
    <v-text-field outlined v-model="username" label="E-Nummer" autocomplete="username" :error="loginError"></v-text-field>
    <v-text-field outlined v-model="password" type="password" autocomplete="password" label="Passwort" :error="loginError"></v-text-field>
    <p>Angaben zu Noten, Terminen und Absenzen ohne Gewähr. Nutzung auf eigene Verantwortung. Zugangsdaten werden verschlüsselt und ausschliesslich auf deinem Gerät gespeichert.</p>
    <v-checkbox label="Damit bin ich einverstanden" v-model="acceptConditions"></v-checkbox>

    <v-btn
      type="submit"
      @click="fetchData"
      :loading="fetchingData"
      :disabled="!acceptConditions || !password || !username || !school"
      class="full-width primary"
    >
      Login
    </v-btn>
  </v-card-text>
  </v-form>
</template>

<script>
export default {
}
</script>

<style lang="css" scoped>
</style>
