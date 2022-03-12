<template>
  <div class="item-page">
    <page-title>
      <v-btn icon :exact-path="true" :to="{name: 'bookstore'}">
        <v-icon>mdi-arrow-left</v-icon>
      </v-btn>
      <div class="ml-2">{{ $t('bookstore.book') }}</div>
    </page-title>
    <div class="scroll-content">
    <div class="mx-5 nav-padding">
    <div v-if="item">
    <v-img class="full-cover" v-if="item.cover" ref="coverEl" :data-image-url="item.cover.data.thumbnails[5].url"  :aspect-ratio="item.cover.width/item.cover.height" :src="item.cover.data.thumbnails[5].url"></v-img>
    <h2 class="item-title">{{ item.title }}</h2>
    <h3 class="item-authors mb-5">{{ item.authors }}</h3>

    <copy-selector v-if="copiesVisible && item.copies" @select="selectCopy" :copies="item.copies"></copy-selector>
    <login ref="loginForm" @success="confirmOrderVisible = true" />

    <v-btn class="primary full-width" v-if="item.copies && item.copies.length > 0" @click="viewCopies()">{{ mainButtonText }}</v-btn>

    <v-dialog
        v-model="confirmOrderVisible"
        scrollable
        transition="dialog-bottom-transition"
        width="500">
      <v-card>
          <v-card-title>
            <v-btn icon class="mr-2" @click="confirmOrderVisible = false"><v-icon>mdi-close</v-icon></v-btn>
            Deine Bestellung
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-0">
            <confirm-order :copy="selectedCopy" :item="item" :main-element="mainElement"></confirm-order>
          </v-card-text>
        </v-card>
    </v-dialog>

    <v-btn class="full-width" v-if="item.copies.length == 0" disabled>Nicht verfügbar :/</v-btn>

    <div class="mb-4"></div>

    <div class="mb-4">
    <v-expansion-panels>
      <v-expansion-panel v-if="item.description">
        <v-expansion-panel-header v-slot="{open}">
          <transition name="fade">
            <div>
              <v-icon class="mr-2">mdi-card-text</v-icon>Beschreibung
            </div>
          </transition>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <div v-html="item.description.replaceAll('\n', '<br>')"></div>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </div>
  </div>
</div></div></div>
</template>

<script>
import api from "../../business/api.js"
import coverTransition from "../../business/coverTransition.js"


import confirmOrder from "../../components/bookstore/ConfirmOrder"
import copySelector from "../../components/bookstore/CopySelector"
import pageTitle from "../../components/PageTitle"
import login from "../../components/dialogs/LoginBookstore"

export default {
  components: {
    confirmOrder,
    copySelector,
    pageTitle,
    login
  },
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

    hasUserInfo(){
      return this.$store.state.user && this.$store.state.user.username
    },

    mainButtonText(){
      if(this.item == undefined){
        return "";
      }
      if(this.selectedCopy == null){
        if(!this.item.copies[0].price){
          return `${this.item.copies.length} Exemplar${(this.item.copies.length > 1 ? 'e' : '')}`
        }
        return `${this.item.copies.length} Exemplar${(this.item.copies.length > 1 ? 'e ab' : ' für')} ${this.item.copies[0].price}.-`
      }
      else{
        if(this.hasUserInfo){
          return this.$t("bookstore.order_now")
        }
        else{
          return this.$t("bookstore.login_to_order")
        }
      }
    }
  },

  methods: {
    viewCopies(){
      if(!this.copiesVisible){
        this.copiesVisible = true
      }
      else{
        if(this.hasUserInfo){
          this.confirmOrderVisible = true
        }
        else{
          this.$refs.loginForm.show()
        }
      }
    },

    selectCopy(copy){
      this.selectedCopy = copy
    },
  },

  async mounted(){
    this.item =  this.$route.params.item

    if(this.item == undefined){
      this.item = (await api.fetch("items/items?fields=*.*&filter[id]="+this.$route.params.item_id)).data[0]
    }

    this.item.copies = (await api.fetch("items/copies?fields=id,condition,edition.*,price,status,uid&filter[status]=available&filter[ordered_by][null]=&sort=price&filter[item]="+this.$route.params.item_id)).data

    coverTransition.setToElement(this.$refs.coverEl.$el)
    if(this.item.copies.length == 1){
      this.copiesVisible = true
    }
  }
}
</script>

<style lang="css" scoped>
</style>
