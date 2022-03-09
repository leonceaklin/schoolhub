<template>
  <v-dialog v-model="showDialog" class="select-subject" scrollable transition="dialog-bottom-transition" width="500">
    <template v-slot:activator="{ on, attrs }">
      <v-btn primary class="mt-5 mb-5 full-width" v-if="!isLoggedIn" v-bind="attrs" v-on="on" color="primary">
        {{ $t('auth.login') }}
      </v-btn>
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
        <login @success="afterLogin"/>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import login from "../Login.vue"

export default {
  data(){
    return {
      showDialog: false,
    }
  },
  components: {
    login,
  },
  computed: {
    isLoggedIn(){
      return this.$store.getters.schoolSystemLoggedIn
    }
  },
  methods: {
    afterLogin(){
      this.showDialog = false
      this.$store.dispatch("fetchSchoolSystemData")
      this.$store.dispatch("startSchoolSystemFetchInterval")
    }
  }
}
</script>

<style lang="css" scoped>
</style>
