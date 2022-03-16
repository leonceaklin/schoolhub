<template>
  <v-form @submit.prevent="login">
  <p class="mb-6 mt-4">
    {{ $t('auth.login_info')}}</p>
    <v-autocomplete outlined return-object :items="schools" item-text="name" label="Schule" v-model="school"></v-autocomplete>
    <div v-if="school != null && school.identifier">
      <p>{{ $t('auth.school_system_info', {system_name: systemName, credentials_name: systemCredentials}) }}</p>
      <v-text-field outlined v-model="username" :label="systemUsername" autocomplete="username" :error="loginError"></v-text-field>
      <v-text-field outlined v-model="password" type="password" autocomplete="password" label="Passwort" :error="loginError"></v-text-field>
    </div>
    <p>{{ $t('auth.login_disclamer') }}</p>
    <v-checkbox :label="$t('auth.accept_conditions')" v-model="acceptConditions"></v-checkbox>

    <v-btn
      type="submit"
      @click="login"
      :loading="loading"
      :disabled="!acceptConditions || !password || !username || !school"
      class="full-width primary"
    >
      Login
    </v-btn>
  </v-form>
</template>

<script>
import api from "../business/api"

export default {
  data(){
    return {
      username: null,
      password: null,
      loading: false,
      loginError: false,

      credentialsToken: this.$store.state.credentialsToken,
      school: this.$store.state.school,

      schools: [],
      acceptConditions: this.$store.state.acceptConditions,

      systems: {
        sal: {
          credentials: "SAL",
          username: this.$t('school_systems.e_number'),
          name: "SAL (SchulNetz)"
        }
      }
    }
  },
  async mounted(){
    await this.fetchSchools()

    if(this.school){
      for(var school of this.schools){
        if(school.identifier == this.school){
          this.selectedSchool = JSON.parse(JSON.stringify(school))
        }
      }
    }
  },
  methods: {
    async login(){
      if(this.loading){
        return false
      }
      this.username = this.username.split("@")[0]
      this.loading = true
        var response = await api.schoolSystemLogin({
          school: this.school,
          username: this.username,
          password: this.password
        })

        if(response.data && response.data.token){
          this.credentialsToken = response.data.token
          this.subjectsDialog = false
          this.loginError = false
          this.username = ""
          this.$emit('success')
        }
        else{
          this.loginError = true
          this.$emit('error')
        }
        this.loading = false
        this.password = ""
    },

    async fetchSchools(){
      var response = await api.fetchSchools()
      if(response.data && response.data.schools){
        this.schools = response.data.schools
      }
    },
  },

  computed: {
    systemId(){
      if(this.selectedSchool){
        return this.selectedSchool.system
      }
      return null
    },

    systemName(){
      if(this.systemId){
        return this.systems[this.systemId].name
      }
    },

    systemUsername(){
      if(this.systemId){
        return this.systems[this.systemId].username
      }
    },

    systemCredentials(){
      if(this.systemId){
        return this.systems[this.systemId].credentials
      }
    },
  },

  watch: {
    credentialsToken(val){
      this.$store.dispatch("setCredentialsToken", val)
    },
    school: {
      deep: true,
      handler(val){
        this.$store.dispatch("setSchool", val)
      }
    },
    acceptConditions(val){
      this.$store.dispatch("setAcceptConditions", val)
    }
  }
}
</script>

<style lang="css" scoped>
</style>
