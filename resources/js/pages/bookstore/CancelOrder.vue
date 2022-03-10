<template>
  <div>
    <page-title>
      <v-btn icon :exact-path="true" :to="{name: 'bookstore'}">
        <v-icon>mdi-arrow-left</v-icon>
      </v-btn>
      <div class="ml-2">{{ $t('bookstore.cancellation') }}</div>
    </page-title>
    <div class="scroll-content">
    <div class="my-2 mx-5 nav-padding">
      <h1>
        <div v-if="!cancelConfirmed">Bist du sicher?</div>
        <div v-if="cancelConfirmed">Alles klar!</div>
      </h1>
        <div v-if="!cancelConfirmed">
          Möchtest du dies wirklich stornieren?
        </div>
        <div v-if="cancelConfirmed">
          Wir haben es storniert.
        </div>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="$router.replace({name: 'bookstore'})">
          {{ cancelConfirmed ? 'Ok' : 'Abbrechen' }}
        </v-btn>
        <v-btn v-if="!cancelConfirmed" class="primary" :loading='loading' @click="cancelOrder">
          Stornieren
        </v-btn>
      </v-card-actions>
  </div>
</div>
</div>
</template>

<script>
import api from "../../business/api.js"

import pageTitle from "../../components/PageTitle"

export default {
  components: {
    pageTitle
  },

  data(){
    return {
      loading: false,
      cancelConfirmed: false,
    }
  },

  computed: {
    app(){
      return window.app
    }
  },

  watch: {

  },

  methods: {
    async cancelOrder(){
      this.loading = true
      var response = await api.cancelOrderByHash(this.$route.params.order_hash)
      this.loading = false
      if(response.data.copy.id){
        this.cancelConfirmed = true
      }
    },

  },

  async mounted(){

  }
}
</script>

<style lang="css" scoped>
</style>
