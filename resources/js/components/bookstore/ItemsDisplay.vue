<template>
  <div class="items-display">
  <div class="items-slider">
    <item-preview v-for="item in items" :key="item.id" :item="item"></item-preview>
  </div></div>
</template>

<script>
import itemPreview from './ItemPreview'

export default {
  components: {
    itemPreview
  },
  props: ["items"]
}
</script>

<style lang="css" scoped>
</style>
