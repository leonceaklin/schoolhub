<template>
  <v-dialog v-model="showDialog" persistent max-width="290">
    <v-card>
      <v-card-title class="text-h5">
        <div v-if="!cancelConfirmed">Bist du sicher?</div>
        <div v-if="cancelConfirmed">Alles klar!</div>
      </v-card-title>
      <v-card-text>
        <div v-if="!cancelConfirmed">
          Möchtest du dies wirklich stornieren?
        </div>
        <div v-if="cancelConfirmed">
          Wir haben es storniert.
        </div>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="showDialog = false">
          {{ cancelConfirmed ? 'Ok' : 'Abbrechen' }}
        </v-btn>
        <v-btn text  v-if="!cancelConfirmed" :loading='loading' @click="cancelOrder">
          Stornieren
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  data(){
    return {
      loading: false,
      cancelConfirmed: false,
      showDialog: false
    }
  },

  computed: {
    app(){
      return window.app
    }
  },

  watch: {
    showDialog(val){
      if(val == false){
        history.replaceState && history.replaceState(null, '', location.pathname + location.search.replace(/[\?&]cancelorder=[^&]+/, '').replace(/^&/, '?'));
      }
    }
  },

  methods: {
    async cancelOrder(){
      this.loading = true
      var response = await bookApi.cancelOrderByHash(this.findGetParameter('cancelorder'))
      this.loading = false
      if(response.data.copy.id){
        this.cancelConfirmed = true
      }
    },

    findGetParameter(parameterName) {
      var result = null,
          tmp = [];
      location.search
          .substr(1)
          .split("&")
          .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
          });
      return result;
    }
  },

  async mounted(){
    if(this.findGetParameter('cancelorder')){
      this.showDialog = true
    }
  }
}
</script>

<style lang="css" scoped>
</style>
