<template lang="html">
  <div class="user-info mt-3">
    <div v-if="user.username">
      <h2>{{ user.first_name }} {{ user.last_name}}</h2>
      <p>({{ user.username }})</p>
      <v-switch v-model="allowNotifications" v-if="showNotificationsOption" :label="$t('general.get_notifications')"/>
    </div>
    <v-skeleton-loader v-if="!user.username" type="article"></v-skeleton-loader>
    <v-btn class="full-width mt-5" @click="logout">Logout</v-btn>
  </div>
</template>

<script>
export default {
  data(){
    return {
      user: this.$store.state.user,
      allowNotifications: this.$store.state.allowNotifications,
      showNotificationsOption: false
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
    },

    allowNotifications(val){
      console.log("TEST")
      this.$store.dispatch("setAllowNotifications", val)
      if(val == true){
        window.OneSignal.push(["registerForPushNotifications"])
        window.OneSignal.push(["setSubscription", true]);
      }
      if(val == false){
        window.OneSignal.push(["setSubscription", false]);
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
  },

  mounted(){
    window.OneSignal.push(() => {
      // If we're on an unsupported browser, do nothing
      if (!window.OneSignal.isPushNotificationsSupported()) {
        return;
      }
      window.OneSignal.isPushNotificationsEnabled((isEnabled) => {
        if (isEnabled) {
          this.showNotificationsOption = true
          this.$store.dispatch("setAllowNotifications", true)
          this.allowNotifications = true
        } else {
          this.showNotificationsOption = true
        }
      });
    });
  }
}
</script>

<style lang="css" scoped>
</style>
