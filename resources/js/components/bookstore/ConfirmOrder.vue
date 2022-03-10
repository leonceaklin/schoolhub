<template>
  <div class="confirm-order">
    <div class="pa-5 order-confirmation">
      <v-img class="full-cover small elevation-2 mt-2" :aspect-ratio="item.cover.width/item.cover.height" :src="item.cover.data.thumbnails[5].url"></v-img>
      <h2 class="item-title">{{ item.title }}</h2>
      <h3 class="item-authors">{{ item.authors }}</h3>

        <div v-if="!orderConfirmed">
          <h2 class="copy-price-large">CHF {{ copy.price }}.-</h2>
          <v-btn class="full-width primary" :loading="loading" @click="confirmOrder()">Bestellung abschliessen</v-btn>
          <p class="text--secondary my-2">Du bestellst als {{ user.first_name }} {{ user.last_name }}. <b>Bezahlung bei der Abholung.</b> Wir senden dir eine E-Mail als Bestätigung, mit der du die Bestellung auch stornieren kannst.</p>
        </div>
      <transition name="fade">
        <div class="order-confirmed" v-if="orderConfirmed">
          <v-icon class="mt-5" large>mdi-check-circle</v-icon>
          <h2 class="my-5">{{ $t('bookstore.order_thanks') }}</h2>
          <p>Du hast von uns eine E-Mail erhalten, die deine Bestellung bestätigt. Du kannst das Buch beim Bookstore PickUp neben dem Lichthof gegen Bezahlung abholen.</p>
          <v-btn class="full-width primary" @click="goBack()">Zurück zum Store</v-btn>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import api from "../../business/api.js"

export default {
  props: ['item', 'copy', 'mainElement'],
  data(){
    return {
      loading: false,
      orderConfirmed: false,
    }
  },

  computed: {
    user(){
      return this.$store.state.user
    }
  },

  watch: {

  },

  methods: {
    async confirmOrder(){
      this.loading = true
      var response = await api.orderCopyById(this.copy.id)
      this.loading = false
      if(response.data.copy.id == this.copy.id){
        this.orderConfirmed = true
      }
    },

    goBack(){
      this.$router.push({name: 'bookstore'})
    }
  },

  async mounted(){

  }
}
</script>

<style lang="css" scoped>
</style>
