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
export default {
  props: ['query'],
  data(){
    return {
      loading: false,
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
      this.loading = true
      var res = (await bookApi.fetch("items/items?fields=*.*&limit=50&sort=-year&q="+this.query)).data

      var results = []
      for(var item of res){
        if(item.copies != null && item.copies.length > 0){
          results.push(item)
        }
      }

      this.results = results
      console.log("Results:", this.results)
      this.loading = false
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
