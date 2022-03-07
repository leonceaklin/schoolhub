<template>

</template>

<script>
export default {
  data(){
    return {
      subjects: [],
      query: '',
    }
  },
  async mounted(){
    if(window.bookstore.page && window.bookstore.page.params.query){
      this.query = window.bookstore.page.params.query
    }

    var response = await bookApi.fetch("items/subjects?fields=id,title")
    var subjects = response.data

    var accountSubjects = []
    var relSubjects = []
    for(var subject of window.app.subjects){
      accountSubjects.push(subject.name)
    }

    for(var subject of subjects){
      if(accountSubjects.includes(subject.title)){
        relSubjects.push(subject)
      }
    }


    subjects = relSubjects

    for(var subject of subjects){
      var response = await bookApi.fetch("items/items?fields=*.*&limit=10&sort=-created_on&&filter[copies][nnull]=&filter[subject]="+subject.id)
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
