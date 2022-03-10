<template>
  <v-app>
    <v-card class="main-content">
        <router-view></router-view>
      <bottom-navigation/>
      <installation-banner/>
    </v-card>
  </v-app>
</template>

<script>
import bottomNavigation from "../components/BottomNavigation"
import loginDialog from "../components/dialogs/Login"
import installationBanner from "../components/InstallationBanner"

export default {
  components: {
    bottomNavigation,
    loginDialog,
    installationBanner
  }
}
</script>

<style lang="css" scoped>
</style>
