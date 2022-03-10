<template>
  <div>
    <transition name="slideUp">
      <div class="slide slide-1" v-if="slideIndex == 1">
        <div class="slogan-text">{{ slogan.text_1 }}</div>
      </div>
    </transition>

    <transition name="slideUp">
      <div class="slide slide-2" v-if="slideIndex == 2">
        <div class="slogan-text">{{ slogan.text_2 }}</div>
      </div>
    </transition>

    <transition name="scale">
      <div class="slide slide-5" v-if="slideIndex == 5">
        <div class="end-card">
          <img :src="baseUrl+'/images/sell.svg'"/>
          <div class="end-title">Der GymLi Bookstore</div>
          <div class="end-subtitle">Secondhand Bücher <b>kaufen</b> und <b>verkaufen</b>.<br>Ein Angebot der SO Gymnasium Liestal.</div>
          <div class="end-title mt-4 store-link">schoolhub.ch</div>
        </div>
      </div>
    </transition>

    <transition name="slideUp">
      <div :class="{slide: true, 'slide-3': slideIndex == 3, 'slide-4': slideIndex == 4}" v-if="slideIndex == 3 || slideIndex == 4">
        <div class="signage-item-preview">
          <div class="item-info">
            <div class="signage-cover-wrapper">
              <div class="cover-effect-wrapper">
                <div class="cover-effect">
                  <img :src="cover.url" v-if="cover" class="signage-cover"/>
                </div>
              </div>
            </div>
          <div class="item-title">{{ item.title }}</div>
          <div class="item-authors">{{ item.authors }}</div>
          </div>

          <div class="price-info">
            <template v-if="item.copies.length > 1">{{ item.copies.length }} Exemplare verfügbar ab CHF {{price}}.-</template>
            <template v-if="item.copies.length == 1">Noch ein Exemplar verfügbar für CHF {{price}}.-</template>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import api from "../../business/api.js"

export default {
  data(){
    return {
      item: null,
      slogan: null,
      subject: null,
      unavailableSlogans: [],
      storeId: 1,

      slideIndex: 0,
      currentVersion: null,
      failedSloganSearches: 0
    }
  },
  async mounted(){
    while(true){
      try{
        await this.startCycle()
      }catch(e){
        break;
        this.reloadWindow()
      }
    }
  },
  computed: {
    baseUrl(){
      return window.baseUrl
    },
    cover(){
      if(this.item && this.item.cover && this.item.cover.data){
        return this.item.cover.data.thumbnails[5]
      }
      return null
    },
    price(){
      if(!this.item || !this.item.copies){
        return null
      }

      var price = 1000000000000;
      for(var copy of this.item.copies){
        if(copy.price < price){
          price = copy.price
        }
      }

      return price
    }
  },
  methods: {
    async startCycle(){
      this.slideIndex = 0
      await this.prepareSlogan()
      await this.sleep(500)
      this.slideIndex = 1
      await this.sleep(3000)
      this.slideIndex = 2
      await this.sleep(4000)
      this.slideIndex = 3
      await this.sleep(2000)
      this.slideIndex = 4
      await this.sleep(5000)
      this.slideIndex = 5
      await this.sleep(7000)
    },

    async prepareSlogan(){
      if(this.failedSloganSearches > 10){
        alert("Sorry…")
        returnw
      }
      await this.versionCheck()
      var data = await api.fetch('items/signage_slogans?fields=id,subject.id,item.id&filter[store]='+this.storeId)
      var slogans = data.data
      var sloganId = null

      while(sloganId == null || this.unavailableSlogans.includes(sloganId)){
        var slogan = slogans[Math.floor(Math.random()*slogans.length)]
        var sloganId = slogan.id
      }

      if(slogan.item != null){
        this.subject = null
        var data = await api.fetch('items/signage_slogans/'+sloganId+'?fields=id,text_1,text_2,item.cover.data,item.copies.price,item.title,item.authors&filter[store]='+this.storeId)
        var slogan = data.data
        var item = slogan.item

        if(item.copies.length == 0){
          this.unavailableSlogans.push(sloganId)
          this.failedSloganSearches++
          return await this.prepareSlogan()
        }
      }
      else{
        var item = null
        var data = await api.fetch('items/signage_slogans/'+sloganId+'?fields=id,text_1,text_2,subject.items.copies.id,subject.items.id&filter[store]='+this.storeId)
        var slogan = data.data
        var subject = slogan.subject

        //Select item
        var itemIds = []
        for(item of subject.items){
          if(item.copies.length > 0){
            itemIds.push(item.id)
          }
        }

        if(itemIds.length == 0){
          this.unavailableSlogans.push(sloganId)
          this.failedSloganSearches++
          return await this.prepareSlogan()
        }

        var itemId = itemIds[Math.floor(Math.random()*itemIds.length)]
        var data = await api.fetch('items/items/'+itemId+'?fields=id,cover.data,title,authors,copies.price')
        var item = data.data

        try{
          var img = new Image();
          img.src = item.cover.data.thumbnails[5].url
        }catch(e){

        }
      }

      this.item = item
      this.slogan = slogan
      this.subject = subject
      this.unavailableSlogans = []
      this.failedSloganSearches = 0
    },

    async versionCheck(){
      try{
        var data = await api.fetch('items/stores/'+this.storeId+'?fields=version')
        var version = data.data.version
        if(this.currentVersion != null && version != this.currentVersion){
          this.reloadWindow()
        }
        else{
          this.currentVersion = version
        }
      }catch(e){
        this.reloadWindow()
      }
    },

    reloadWindow(){
      window.location.reload()
    },

    sleep(ms){
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve()
        }, ms)
      })
    },


  },
  watch: {

  }
}
</script>

<style lang="css" scoped>
</style>
