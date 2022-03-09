<template>
  <div>
    <div class="page-title">
      {{ $t('bookstore.bookstore') }}
    </div>
  <div class="subjects-page mx-5">
    <v-btn class="full-width mt-2 mb-2" :to="{name: 'bookstore.sell'}">Etwas verkaufen <v-icon right dark>mdi-chevron-right</v-icon></v-btn>
    <v-text-field outlined label="Finden" class="mt-1" append-icon="mdi-magnify" v-model="query" clearable></v-text-field>
    <search-results v-if="query != '' && query != null" :query="query"></search-results>
    <v-skeleton-loader v-if="relevantSubjects.length == 0" type="article@3"></v-skeleton-loader>
    <div v-if="query == '' || query == null" v-for="subject in relevantSubjects" :key="subject.id">
      <h2>{{ subject.title }}</h2>
      <items-display :items="subject.items">
      </items-display>
    </div>
  </div></div>
</template>

<script>
import api from "../../business/api"

import itemsDisplay from "../../components/bookstore/ItemsDisplay"

export default {
  components: {
    itemsDisplay
  },
  data(){
    return {
      subjects: [],
      appSubjects: this.$store.state.subjects,
      query: '',
    }
  },
  async mounted(){
    if(this.$route.params.query){
      this.query = this.$route.params.query
    }

    var response = await api.fetch("items/subjects?fields=id,title")
    var subjects = response.data

    var accountSubjects = []
    var relSubjects = []
    for(var subject of this.appSubjects){
      accountSubjects.push(subject.name)
    }

    for(var subject of subjects){
      if(accountSubjects.includes(subject.title)){
        relSubjects.push(subject)
      }
    }


    if(relSubjects.length > 0){
      subjects = relSubjects
    }

    for(var subject of subjects){
      var response = await api.fetch("items/items?fields=*.*&limit=10&sort=-created_on&&filter[copies][nnull]=&filter[subject]="+subject.id)
      var itms = response.data
      var items = []
      for(var item of itms){
        if(item.copies != null && item.copies.length > 0){
          items.push(item)
        }
      }

      subject.items = items
    }
    this.subjects = subjects
  },
  watch: {
    query(val){
      window.bookstore.page.params.query = val
    },
  },
  computed: {
    relevantSubjects(){
      return this.subjects
    }
  },
  methods: {
    goToSellItemPage(){
      window.bookstore.goTo({
        name: "sell-item",
        params: {},
      })
    }
  }
}
</script>

<style lang="css" scoped>
</style>
