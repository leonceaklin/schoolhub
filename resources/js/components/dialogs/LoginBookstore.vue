<template>
  <div>
  <v-dialog v-model="showDialog" class="select-subject" scrollable transition="dialog-bottom-transition" width="500">
    <template v-slot:activator="{ on, attrs }">
    </template>
    <v-card>
      <v-card-title>
        <v-btn icon class="mr-2" @click="showDialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        {{ $t('auth.login') }}
      </v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <login v-if="!loginSucceeded" @success="afterLogin"/>
        <v-skeleton-loader v-else type="article@3"></v-skeleton-loader>
      </v-card-text>
    </v-card>
  </v-dialog>
</div>
</template>

<script>
import login from "../Login.vue"

export default {
  data(){
    return {
      showDialog: false,
      loginSucceeded: false
    }
  },
  components: {
    login,
  },
  computed: {
    isLoggedIn(){
      return this.$store.getters.schoolSystemLoggedIn
    },

    hasUserInfo(){
      return this.$store.state.user && this.$store.state.user.username
    }
  },
  watch: {
    hasUserInfo(val){
      if(val){
        this.showDialog = false
        this.$emit("success")
      }
    }
  },
  methods: {
    show(){
      this.showDialog = true
    },
    afterLogin(){
      this.loginSucceeded = true
      this.$store.dispatch("fetchSchoolSystemData")
      this.$store.dispatch("startSchoolSystemFetchInterval")
    }
  }
}
</script>

<style lang="css" scoped>
</style>
