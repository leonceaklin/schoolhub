<template>
  <div>
    <v-btn icon v-if="isLoggedIn" @click="showDialog = true" class="user-info">
      <v-icon>mdi-account</v-icon>
    </v-btn>
    <v-dialog v-model="showDialog" class="select-subject" scrollable transition="dialog-bottom-transition" width="500">
      <v-card>
        <v-card-title>
          <v-btn icon class="mr-2" @click="showDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          {{ $t('auth.me') }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <user-editor/>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import userEditor from "../User"

export default {
  components: {
    userEditor
  },
  data(){
    return {
      showDialog: false,
    }
  },
  computed: {
    isLoggedIn(){
      return this.$store.getters.schoolSystemLoggedIn
    }
  },

  watch: {
    isLoggedIn(val){
      if(!val){
        this.showDialog = false
      }
    }
  },

}
</script>

<style lang="css" scoped>
</style>
