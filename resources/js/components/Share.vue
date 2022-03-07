<template>
  <v-card class="primary pa-4 mt-4" dark>
  <h3>{{ $t('general.like_it') }}</h3>
  {{ $t('general.share_with_friends') }}
  <v-btn class="d-block full-width mt-3" text @click="shareApp"><v-icon class="mr-2">mdi-share</v-icon>Teilen</v-btn>
  </v-card>
</template>

<script>
export default {
  data(){
    return {
      shareData: {
        title: this.$t('general.share_text'),
        url: "https://schoolhub.ch?mtm_campaign=Share"
      },
    }
  },
  methods: {
    async shareApp(){
      try {
        await navigator.share(this.shareData)
      } catch(err) {
      }
    },
  }
}
</script>

<style lang="css" scoped>
</style>
