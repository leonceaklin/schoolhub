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
          <p>{{ $t('general.notifications_question') }}</p>
          <v-btn class="primary full-width mb-3" @click="subscribe">{{ $t('general.notifications_yes') }}</v-btn>
          <v-btn class="full-width" text @click="deny">{{ $t('general.notifications_no') }}</v-btn>
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
      allowNotifications: this.$store.state.allowNotifications
    }
  },
  computed: {
    baseUrl(){
      return window.baseUrl
    }
  },
  methods: {
    subscribe(){
      this.$store.dispatch("setAllowNotifications", true)
      window.OneSignal.push(["registerForPushNotifications"]);
      this.showDialog = false
    },
    deny(){
      this.$store.dispatch("setAllowNotifications", false)
    }
  },
  mounted(){
    if(this.allowNotifications == false){
      return
    }

    window.OneSignal.push(() => {
      // If we're on an unsupported browser, do nothing
      if (!window.OneSignal.isPushNotificationsSupported()) {
        return;
      }
      window.OneSignal.isPushNotificationsEnabled((isEnabled) => {
        if (isEnabled) {
          // The user is subscribed to notifications
          // Don't show anything
        } else {
          this.showDialog = true
        }
      });
    });
  }
}
</script>

<style lang="css" scoped>
</style>
