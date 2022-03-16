<template>
  <div class="search-results">
    <div class="mb-3">
    <v-skeleton-loader v-if="loading" type="article@2"></v-skeleton-loader>
    <div class="items-grid">
    <item-preview v-for="item in results" :key="item.id" :item="item"></item-preview></div>
    <div v-if="!loading && results.length == 0">
      Wir haben nichts gefunden. Versuch es mit einem anderen Suchbegriff.
    </div>
    </div>
  </div>
</template>

<script>
import api from "../../business/api.js"
import itemPreview from "./ItemPreview"

export default {
  props: ['query'],
  components: {
    itemPreview
  },
  data(){
    return {
      loading: false,
      trackedQuery: "",
      searchTrackTimeout: null
    }
  },

  computed: {

  },

  watch: {
    query(value){
      if(value != ''){
        this.fetchResults()
      }
    },
  },

  methods: {
    async fetchResults(){
      this.clearTrackTimeout();
      this.loading = true
      var res = (await api.fetch("items/items?fields=*.*&limit=50&sort=-year&q="+this.query)).data

      var results = []
      for(var item of res){
        if(item.copies != null && item.copies.length > 0){
          results.push(item)
        }
      }

      this.results = results
      this.setTrackTimeout();
      console.log("Results:", this.results)
      this.loading = false
    },

    setTrackTimeout(){
      this.clearTrackTimeout();
      this.searchTrackTimeout = setTimeout(() => {this.trackSearch()}, 3000)
    },

    clearTrackTimeout(){
      clearTimeout(this.searchTrackTimeout)
    },

    trackSearch(){
      if(this.query != this.trackedQuery){
        window._paq.push(['trackSiteSearch', this.query, null, this.results.length])
      }

      this.trackedQuery = this.query
    },
  },

  async mounted(){
    if(this.query != ''){
      this.fetchResults()
    }
  }
}
</script>

<style lang="css" scoped>
</style>
