<template>
  <div>
    <transition name="slideUp">
      <div class="slide slogan-1" v-if="slideIndex == 'slogan-1'">
        <div class="slogan-text">{{ slogan.text_1 }}</div>
      </div>
    </transition>

    <transition name="slideUp">
      <div class="slide slogan-2" v-if="slideIndex == 'slogan-2'">
        <div class="slogan-text">{{ slogan.text_2 }}</div>
      </div>
    </transition>

    <transition name="scale">
      <div class="slide end-card" v-if="slideIndex == 'end-card'">
        <div class="end-card">
          <img :src="baseUrl+'/images/sell.svg'"/>
          <div class="end-title">Der GymLi Bookstore</div>
          <div class="end-subtitle">Secondhand Bücher <b>kaufen</b> und <b>verkaufen</b>.<br>Ein Angebot der SO Gymnasium Liestal.</div>
          <div class="end-title mt-4 store-link">schoolhub.ch/bookstore</div>
        </div>
      </div>
    </transition>

    <transition name="slideUp">
      <div :class="{slide: true, 'book': slideIndex == 'book', 'book-copies': slideIndex == 'book-copies'}" v-if="slideIndex == 'book' || slideIndex == 'book-copies'">
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

    <transition name="scale">
      <div class="slide end-card" v-if="slideIndex == 'store-open'">
        <div class="end-card">
          <img :src="baseUrl+'/images/sell.svg'"/>
          <div class="end-title">Der GymLi Bookstore ist jetzt geöffnet!<br>Du kannst deine Bestellungen abholen.</div>
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
      cycleLength: 6,
      cycleStep: 1,
      store: null,

      slideIndex: 0,
      currentVersion: null,
      failedSloganSearches: 0
    }
  },
  async mounted(){
    while(true){
      try{
        await this.startCycleStep()
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
    async startCycleStep(){
      await this.versionCheck()
      this.slideIndex = 0

      if(this.store.status == 'open' && this.cycleStep == 1){
        await this.sleep(500)
        this.slideIndex = 'store-open'
        await this.sleep(10000)
      }

      

      // BookShow
      else{
        await this.prepareSlogan()
        await this.sleep(500)
        this.slideIndex = 'slogan-1'
        await this.sleep(3000)
        this.slideIndex = 'slogan-2'
        await this.sleep(4000)
        this.slideIndex = 'book'
        await this.sleep(2000)
        this.slideIndex = 'book-copies'
        await this.sleep(5000)
        this.slideIndex = 'end-card'
        await this.sleep(7000)
      }

      this.cycleStep++
    },

    async prepareSlogan(){
      if(this.failedSloganSearches > 10){
        alert("Sorry…")
        returnw
      }
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
        var data = await api.fetch('items/stores/'+this.storeId)
        this.store = data.data
        var version = store.version
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
      //window.location.reload()
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
    cycleStep(val){
      if(val > this.cycleLength){
        this.cycleStep = 0
      }
    }
  }
}
</script>

<style lang="css" scoped>
</style>
