<template>
  <v-app>
    <v-card class="main-content">
      <div class="px-5">
        <login-dialog/>
      </div>
      <router-view></router-view>
      <bottom-navigation/>
    </v-card>
  </v-app>
</template>

<script>
import bottomNavigation from "../components/BottomNavigation.vue"
import loginDialog from "../components/dialogs/Login.vue"

export default {
  components: {
    bottomNavigation,
    loginDialog
  }
}
</script>

<style lang="css" scoped>
</style>
