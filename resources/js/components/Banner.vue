<template>
  <div>
    <div v-if="banner">
      <v-card rounded elevation="2" class="mb-4 full-width">
        <v-img :src="banner.image.data.full_url" :aspect-ratio="banner.image.width/banner.image.height" v-if="banner.image"></v-img>
        <v-card-text class="text--secondary mb-0 pb-0">{{ banner.surtitle }}</v-card-text>
        <v-card-title v-if="banner.title" :class="{'font-weight-bold': true, 'pt-0': banner.surtitle}">{{ banner.title }}</v-card-title>
        <v-card-text v-if="banner.introduction">{{ banner.introduction }}</v-card-text>
        <v-card-actions><v-btn text color="primary" @click="showDetails" :href="banner.action_link" v-if="banner.action_button_text">{{ banner.action_button_text }}</v-btn></v-card-actions>
      </v-card>


      <v-dialog
          v-model="showBannerDetails"
          scrollable
          transition="dialog-bottom-transition"
          width="500">
        <v-card>
            <v-card-title>
              <v-btn icon class="mr-2" @click="showBannerDetails = false"><v-icon>mdi-close</v-icon></v-btn>
              {{ banner.title }}
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text class="pa-4 banner-info-text" v-html="banner.content">
            </v-card-text>
          </v-card>
      </v-dialog>
    </div>
  </div>
</template>

<script>
import api from "../business/api"

export default {
  props: ['place'],
  data(){
    return {
      banner: null,
      showBannerDetails: false
    }
  },
  mounted(){
    this.fetchBanner()
  },

  methods: {
    async fetchBanner(){
      var data = await api.fetch("items/banners?fields=*.*&filter[place]="+this.place)
      var banners = data.data
      var banner = data.data[0]
      if(banner != undefined){
        this.banner = banner
      }
    },

    showDetails(){
      if(this.banner.content != '' && this.banner.content != null){
        this.showBannerDetails = true

        var goalId = null
        if(this.place == 'bookstore'){
          goalId = 7
        }

        if(goalId != null){
          _paq.push(['trackGoal', goalId]);
        }
      }
    }
  }
}
</script>

<style lang="css" scoped>
</style>
