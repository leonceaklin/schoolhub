<template>
  <div class="confirm-order">
    <div class="pa-5 order-confirmation">
      <v-img class="full-cover small elevation-2 mt-2" :aspect-ratio="item.cover.width/item.cover.height" :src="item.cover.data.thumbnails[5].url"></v-img>
      <h2 class="item-title">{{ item.title }}</h2>
      <h3 class="item-authors">{{ item.authors }}</h3>

        <div v-if="!orderConfirmed">
          <h2 class="copy-price-large">CHF {{ copy.price }}.-</h2>
          <div class="mb-2" v-if="copy.charity != null && (copy.charity_commission > 0 || copy.donation == true)">
            <h3 class="item-authors mb-2">{{ $t("bookstore.donation_info", {amount: copy.donation ? "CHF "+copy.price+".-" : "CHF "+Math.round(copy.price*copy.commission*copy.charity_commission*100)/100}) }}</h3>
            <v-img v-if="copy.charity.logo" class="mx-auto mb-6 charity-logo-small" :alt="copy.charity.name" :src="copy.charity.logo.data.full_url" />
          </div>
          <v-btn class="full-width primary" :loading="loading" @click="confirmOrder()">{{ $t("bookstore.confirm_order") }}</v-btn>
          <p class="text--secondary my-2">{{ $t("bookstore.you_order_as", {name: user.first_name + " " + user.last_name}) }} <b>{{ $t("bookstore.payment_on_pickup") }}</b><br>{{ $t("bookstore.order_email_info") }}</p>
        </div>
      <transition name="fade">
        <div class="order-confirmed" v-if="orderConfirmed">
          <v-icon class="mt-5" large>mdi-check-circle</v-icon>
          <h2 class="my-5">{{ $t('bookstore.order_thanks') }}</h2>
          <p>{{ $t("bookstore.after_payment_info") }}</p>
          <v-btn class="full-width primary" @click="goBack()">{{ $t("bookstore.back_to_store") }}</v-btn>
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
