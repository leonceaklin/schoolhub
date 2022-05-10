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
        {{ $t('general.get_notification') }}
      </v-card-title>
      <v-card-text>
        <div class="notification-banner">
          <v-icon size="xl">mdi-bell</v-icon>
          <div v-if="!askingForPermission">
            <p>{{ $t('general.notifications_question') }}</p>
            <v-btn class="primary full-width mb-3" @click="subscribe">{{ $t('general.notifications_yes') }}</v-btn>
            <v-btn class="full-width" text @click="deny">{{ $t('general.notifications_no') }}</v-btn>
          </div>
          <div v-else>
            <p>{{ $t('general.notifications_native_prompt_info') }}</p>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</div>
</template>

<script>
export default {
  data(){
    return{
      showDialog: false,
      loggedIn: this.$store.getters.schoolSystemLoggedIn,
      askingForPermission: false
    }
  },
  computed: {
    baseUrl(){
      return window.baseUrl
    }
  },
  watch: {
    loggedIn(val){
      if(val == true){
        this.checkShowBanner()
      }
    }
  },
  methods: {
    subscribe(){
      this.$store.dispatch("setAllowNotifications", true)
      this.askingForPermission = true
      window.OneSignal.push(["registerForPushNotifications"]);
      window.OneSignal.push(["setSubscription", true]);

      window.OneSignal.push(["getNotificationPermission", (permission) => {
          if(permission == "granted"){
            this.showDialog = false
            window.OneSignal.push(["setSubscription", true]);
          }
      }]);

      window.OneSignal.push(() => {
        window.OneSignal.on('notificationPermissionChange', (permissionChange) => {
          var permission = permissionChange.to;
          if(permission == "granted"){
            this.showDialog = false
            window.OneSignal.push(["setSubscription", true]);
          }
          if(permission == "default"){
            window.OneSignal.push(["registerForPushNotifications"]);
          }
          else{
            this.showDialog = false
            window.localStorage.setItem("isOptedOut", true)
          }
        });
      });

    },
    deny(){
      this.$store.dispatch("setAllowNotifications", false)
      this.showDialog = false
      window.OneSignal.push(["setSubscription", false]);
    },

    checkShowBanner(){
      var optedOut = window.localStorage.getItem("isOptedOut")
      console.log(optedOut)
      if(optedOut == true){
        this.showDialog = false
        return
      }

      window.OneSignal.push(() => {
        // If we're on an unsupported browser, do nothing
        if (!window.OneSignal.isPushNotificationsSupported()) {
          this.showDialog = false
          return;
        }
        window.OneSignal.isPushNotificationsEnabled((isEnabled) => {
          if (isEnabled) {
            // The user is subscribed to notifications
            this.showDialog = false
          } else {
            this.showDialog = true
          }
      });
      });
    }
  },
  mounted(){
    if(this.loggedIn){
      this.checkShowBanner()
    }
  }
}
</script>

<style lang="css" scoped>
</style>
