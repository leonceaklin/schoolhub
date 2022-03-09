<template>

</template>

<script>
import api from "../../business/api.js"


export default {
  data(){
    return {
      item: null,
      copiesVisible: false,
      confirmOrderVisible: false,
      selectedCopy: null,
    }
  },

  computed: {
    mainElement(){
      return this
    },

    bookstore(){
      return window.bookstore
    },

    mainButtonText(){
      if(this.selectedCopy == null){
        if(!this.item.copies[0].price){
          return `${this.item.copies.length} Exemplar${(this.item.copies.length > 1 ? 'e' : '')}`
        }
        return `${this.item.copies.length} Exemplar${(this.item.copies.length > 1 ? 'e ab' : ' für')} ${this.item.copies[0].price}.-`
      }
      else{
        return "Jetzt bestellen"
      }
    }
  },

  methods: {
    viewCopies(){
      if(!this.copiesVisible){
        this.copiesVisible = true
      }
      else{
        this.confirmOrderVisible = true
      }
    },

    selectCopy(copy){
      this.selectedCopy = copy
    }
  },

  async mounted(){
    this.item =  window.bookstore.page.params.item
    this.item.copies = (await api.fetch("items/copies?fields=id,condition,edition.*,price,status&filter[status]=available&filter[ordered_by][null]=&sort=price&filter[item]="+window.bookstore.page.params.item_id)).data

    if(this.item.copies.length == 1){
      this.copiesVisible = true
    }
  }
}
</script>

<style lang="css" scoped>
</style>
