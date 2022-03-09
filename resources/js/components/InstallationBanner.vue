<template>
  <v-snackbar multi-line v-model="installationBanner" color="primary" :timeout="-1">
    <p>
      Füge mich zu deinem Home-Bildschirm hinzu!
    </p>
    <p class="d-block" v-if="!installationPrompt">Tippe dafür auf <v-icon>mdi-export-variant</v-icon> und anschliessend auf <v-icon>mdi-plus-box</v-icon> Zum Home-Bildschirm.</p>
    <v-btn v-if="installationPrompt" class="d-block full-width" text ripple @click="installApp"><v-icon class="mr-2">mdi-plus-box</v-icon>Installieren</v-btn>
    <template v-slot:action>
      <v-btn icon @click="installationBanner = false"><v-icon>mdi-close</v-icon></v-btn>
    </template>
  </v-snackbar>
</template>

<script>
export default {
  data(){
    return {
      installationBanner: false,
      installationPrompt: null,
    }
  },

  mounted(){
    this.checkInstallation()
  },

  methods: {
    checkInstallation(){
      if(this.getDisplayMode() != "browser"){
        return;
      }

      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        this.installationPrompt = e;
        this.installationBanner = true
      });

      if(navigator.userAgent.indexOf("iPhone") !== -1 || navigator.userAgent.indexOf("iPad") !== -1 || (navigator.userAgent.match(/Mac/) && navigator.maxTouchPoints && navigator.maxTouchPoints > 2)){
        setTimeout(() => {
          this.installationBanner = true
        }, 10000)
      }
    },
    installApp(){
      this.installationPrompt.prompt();
      this.installationBanner = false;
    },
    getDisplayMode(){
      const isStandalone = window.matchMedia('(display-mode: standalone)').matches;
      if(document.referrer.startsWith('android-app://')) {
        return 'twa';
      }
      else if (navigator.standalone || isStandalone) {
        return 'standalone';
      }
      return 'browser';
    }
  }
}
</script>

<style lang="css" scoped>
</style>
