<template>

</template>

<script>
export default {
  data(){
    return {
      page:{
        name: "subjects",
        params: {}
      },
      history: [],
    }
  },
  methods: {
    goTo(page){
      this.history.push(this.page)
      this.page = page
      console.log(this.history)
    },
    goBack(){
      this.page = this.history.pop()
    }
  },
  mounted(){
    window.bookstore = this
    _paq.push(['trackGoal', 4])
  }
}
</script>

<style lang="css" scoped>
</style>
