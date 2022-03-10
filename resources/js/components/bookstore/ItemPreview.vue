<template>
<div class="item-preview">
  <router-link :to="{name: 'bookstore.item', params: {item_id: item.id, item: item}}">
  <div class="preview-container">
    <v-img class="preview-cover elevation-2" :src="item.cover.data.thumbnails[3].url"></v-img>
    <div class="item-title-preview">{{ item.title }}</div>
    <div class="item-authors-preview">{{ item.authors }}</div>
  </div>
  </router-link>
</div>
</template>

<script>
export default {
  props: ["item"],
  methods: {
    viewItem(item){
      window.bookstore.goTo({
        name: "item",
        params: {item_id: item.id, item: item}
      })
    }
  }
}
</script>

<style lang="css" scoped>
</style>
