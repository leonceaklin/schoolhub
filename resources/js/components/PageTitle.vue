<template>
<div class="page-title-wrapper">
  <div :class="{'page-title': true, 'no-fix': noFix, 'scrolled': this.scrolled}">
    <slot/>
  </div>
</div>
</template>

<script>
export default {
  data(){
    return {
      scrolled: false
    }
  },
  props: {
    noFix: {
      type: Boolean
    }
  },

  mounted(){
    this.scrollListener()
    this.onScroll()
  },

  beforeDestroy(){
    this.unregisterListener()
  },

  methods: {
    onScroll(e){
      if(e){
        this.scrolled = e.target.scrollTop > 0 || window.pageYOffset > 0
      }
      else{
        this.scrolled = window.pageYOffset > 0
      }
    },

    scrollListener(){
      document.body.onscroll = (e) => {
        this.onScroll(e)
      }
      for(var el of this.getScrollContent()){
        el.addEventListener("scroll", (e) => {this.onScroll(e)}, {passive: true})
      }
    },

    unregisterListener(){
      for(var el of this.getScrollContent()){
        el.removeEventListener("scroll", (e) => {this.onScroll(e)}, {passive: true})
      }
    },

    getScrollContent(){
      var elements = [
        document
      ]
      var el = document.getElementsByClassName("scroll-content")
      for(var element of el){
        elements.push(element)
      }
      return elements
    }
  }
}
</script>

<style lang="css" scoped>
</style>
