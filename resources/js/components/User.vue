<template lang="html">
  <div class="user-info mt-3">
    <div v-if="user.username">
      <h2>{{ user.first_name }} {{ user.last_name}}</h2>
      <p>({{ user.username }})</p>
    </div>
    <v-skeleton-loader v-if="!user.username" type="article"></v-skeleton-loader>
    <v-btn class="full-width" @click="logout">Logout</v-btn>
  </div>
</template>

<script>
export default {
  data(){
    return {
      user: this.$store.state.user
    }
  },

  computed:{
    watchUser(){
      return this.$store.state.user
    }
  },

  watch: {
    watchUser(val){
      if(val.username){
        this.user = val
      }
    }
  },

  methods: {
    logout(){
      this.$store.dispatch("logout")
      if(this.$route.name != "calculator"){
        this.$router.push({name: calculator});
      }
    }
  }
}
</script>

<style lang="css" scoped>
</style>
