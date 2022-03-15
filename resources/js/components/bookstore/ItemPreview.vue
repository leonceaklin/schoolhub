<template>
<div class="item-preview">
  <a ref="link" :href="'/bookstore/'+item.slug">
  <div class="preview-container">
    <v-img class="preview-cover elevation-2" ref="cover" :data-image-url="item.cover.data.thumbnails[5].url" :src="item.cover.data.thumbnails[3].url"></v-img>
    <div class="item-title-preview">{{ item.title }}</div>
    <div class="item-authors-preview">{{ item.authors }}</div>
  </div>
</a>
</div>
</template>

<script>
import coverTransition from "../../business/coverTransition.js"

export default {
  props: ["item"],
  data(){
    return{

    }
  },
  methods: {
    viewItem(item){
      window.bookstore.goTo({
        name: "item",
        params: {item_slug: item.slug, item: item}
      })
    },

    initCoverTransition(){
      coverTransition.setFromElement(this.$refs.cover.$el)
    },

    onLinkClick(e){
      e.preventDefault()
      e.stopPropagation()
      this.initCoverTransition()
      this.$router.push({name: 'bookstore.item', params: {item_slug: this.item.slug, item: this.item}})
    }
  },

  mounted(){
    this.$refs.link.addEventListener('click', (e) => {this.onLinkClick(e)})
  }
}
</script>

<style lang="css" scoped>
</style>
