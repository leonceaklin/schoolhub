var themeColor = "#308efc"

const app = new Vue({
    el: "#app",
    vuetify: new Vuetify({
      theme: {
        themes: {
          light: {
            primary: themeColor
          },
          dark: {
            primary: themeColor
          }
        }
      }
    }),
    data(){
      return {
        item: null,
        slogan: null,
        subject: null,
        unavailableSlogans: [],
        storeId: 1,

        slideIndex: 0,
        currentVersion: null,
      }
    },
    async mounted(){
      // Show App
      document.getElementById("app").style.display = "block"

      while(true){
        try{
        await this.startCycle()
        }catch(e){
          window.location.reload()
        }
      }
    },
    computed: {
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
        await this.versionCheck()
        var data = await bookApi.fetch('items/signage_slogans?fields=id,subject.id,item.id&filter[store]='+this.storeId)
        var slogans = data.data
        var sloganId = null

        while(sloganId == null || this.unavailableSlogans.includes(sloganId)){
          var slogan = slogans[Math.floor(Math.random()*slogans.length)]
          var sloganId = slogan.id
        }

        if(slogan.item != null){
          this.subject = null
          var data = await bookApi.fetch('items/signage_slogans/'+sloganId+'?fields=id,text_1,text_2,item.cover.data,item.copies.price,item.title,item.authors&filter[store]='+this.storeId)
          var slogan = data.data
          var item = slogan.item

          if(item.copies.length == 0){
            this.unavailableSlogans.push(sloganId)
            return await this.prepareSlogan()
          }
        }
        else{
          var item = null
          var data = await bookApi.fetch('items/signage_slogans/'+sloganId+'?fields=id,text_1,text_2,subject.items.copies.id,subject.items.id&filter[store]='+this.storeId)
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
            return await this.prepareSlogan()
          }

          var itemId = itemIds[Math.floor(Math.random()*itemIds.length)]
          var data = await bookApi.fetch('items/items/'+itemId+'?fields=id,cover.data,title,authors,copies.price')
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
      },

      async versionCheck(){
        try{
          var data = await bookApi.fetch('items/stores/'+this.storeId+'?fields=version')
          var version = data.data.version
          if(this.currentVersion != null && version != this.currentVersion){
            window.location.reload()
          }
          else{
            this.currentVersion = version
          }
        }catch(e){
          window.location.reload()
        }

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
});


if("serviceWorker" in navigator){
  navigator.serviceWorker.register("sw.js").then(function(registration){
    console.log("Service Worker registriert");
  }).catch(function(error){
    console.log("Service Worker nicht registriert. Fehler: ",error);
  });
}


var darkModeQuery = window.matchMedia("(prefers-color-scheme: dark)");


app.$vuetify.theme.dark = darkModeQuery.matches

darkModeQuery.addEventListener( "change", (e) => {
    app.$vuetify.theme.dark = e.matches
})

var mouseHideTimeout = null
document.addEventListener('mousemove', () => {
  document.body.style.cursor = "auto !important"
  mouseHideTimeout = setTimeout(() => {
    hideMouse()
  }, 2000)
})

function hideMouse(){
  document.body.style.cursor = "none"
}

hideMouse()

window.app = app
