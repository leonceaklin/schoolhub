<template>
<div>
  <v-sheet dark v-if="show" rounded elevation="2" v-ripple class="event-tickets-sheet mb-5 pa-5 full-width" @click="eventsDialog = true">
    {{ $t("events.party_presale") }}
  </v-sheet>


  <v-dialog
      v-model="eventsDialog"
      scrollable
      transition="dialog-bottom-transition"
      width="600">
    <v-card>
        <v-card-title>
          <v-btn icon class="mr-2" @click="eventsDialog = false"><v-icon>mdi-close</v-icon></v-btn>
          {{ $t("events.party_presale") }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-4">
          <div class="mb-4">{{ $t("events.party_presale_matura_info") }}</div>

          <v-card v-for="event in events" @click="showEventDetails = true; selectedEvent = event" v-key="event.id">
            <v-img :src="event.image.data.full_url" :aspect-ratio="event.image.width/event.image.height"></v-img>
            <v-card-title class="font-weight-bold">{{ event.name }}</v-card-title>
            <v-card-text class="font-weight-bold">
              <p><v-icon class="mr-1">mdi-calendar</v-icon> {{ (new Date(event.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', year: 'numeric', month: 'long', day: 'numeric'}) }}</p>
              <p><v-icon class="mr-1">mdi-map-marker</v-icon> {{ event.location }}</p>
              <div><v-btn class="primary">{{ event.price ? $t("events.tickets_for", {price: "CHF "+event.price + ".-"}) : $t("event.tickets")}}</v-btn></div>
            </v-card-text>
          </v-card>

        </v-card-text>
    </v-card>
  </v-dialog>


  <v-dialog
      v-model="showEventDetails"
      scrollable
      transition="dialog-bottom-transition"
      width="600">
    <v-card>
        <v-card-title v-if="selectedEvent">
          <v-btn icon class="mr-2" @click="showEventDetails = false" v-if="embedHistoryLength <= 0"><v-icon>mdi-close</v-icon></v-btn>
          <v-btn icon class="mr-2" @click="embedGoBack" v-else><v-icon>mdi-arrow-left</v-icon></v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-0">
          <v-skeleton-loader class="ma-4" v-if="!embedLoaded" type="card,article@2"></v-skeleton-loader>
          <div :class="{'embed-is-loading': !embedLoaded, 'pa-4': embedNeedsPadding}">
            <iframe :class="{'ticketing-embed': true}" @load="onEmbedLoad" :src="embedUrl" ref="embedIframe" v-if="embedUrl != null"></iframe>
          </div>
        </v-card-text>
      </v-card>
  </v-dialog>
</div>

</template>

<script>
import api from '../business/api'

export default {
  data(){
    return {
      eventsDialog: false,
      showEventDetails: false,
      selectedEvent: null,

      events: [],
      embedUrl: null,
      embedLoaded: false,
      embedNeedsPadding: false,
      embedHistoryLength: -1,
      nextLoadIsHistoryBack: false,
    }
  },

  mounted(){
    if(this.schoolId != null){
      this.fetchEvents()
    }

    window.addEventListener('popstate', (e) => {this.onHistoryChange(e)})
  },

  beforeDestroy(){
    window.removeEventListener('popstate', (e) => {this.onHistoryChange(e)})
  },

  watch: {
    showEventDetails(val){
      if(val == true){
        _paq.push(['trackGoal', 8]);

        this.$nextTick(() => {
          this.setupEventEmbed()
        })
      }
    },

    embedUrl(){
      this.embedLoaded = false
    },

    schoolId(val){
      if(val != null && val != undefined){
        this.fetchEvents()
      }
    },

    username(val){
      if(val != null && val != undefined){
        this.fetchEvents()
      }
      else{
        this.events = []
      }
    }
  },

  methods: {
    async fetchEvents(){
      if(this.$store.state.user.username == undefined){
        return false
      }
      var data = await api.fetch("items/events?fields=*.*&sort=start&filter[school]="+this.schoolId)
      var events = data.data
      var defEvents = []
      for(var event of events){
        console.log((new Date(event.start.replaceAll("-", "/"))).getTime())
        if((new Date(event.start.replaceAll("-", "/"))).getTime() > Date.now()){
          defEvents.push(event)
        }
      }

      this.events = defEvents
    },

    setupEventEmbed(){
      var event = this.selectedEvent
      var embedUrl = this.getEmbedUrl(event)
      this.embedLoaded = false

      this.embedUrl = null
      this.embedHistoryLength = -1

      this.$nextTick(() => {
        this.embedUrl = embedUrl
        var embedScript = document.getElementById("eventfrog-script")
        if(embedScript == undefined){
          embedScript = document.createElement("script")
          embedScript.src = "https://embed.eventfrog.ch/api/scripts/embed/event.js"
          document.body.append(embedScript)
        }
      })
    },

    getEmbedUrl(event){
      var normalUrl = event.eventfrog_url
      var url = normalUrl.replace("https://eventfrog.ch", "https://embed.eventfrog.ch")
      url = url + '?color=308efc&infobox=1&description=1&location=1&organisator=0&sponsors=1'
      return url
    },

    onEmbedLoad(e){
      this.embedLoaded = true

      if(!this.nextLoadIsHistoryBack){
        this.embedHistoryLength++
      }
      this.nextLoadIsHistoryBack = false

      if(this.embedHistoryLength <= 0){
        this.embedNeedsPadding = true
      }

      else{
        this.embedNeedsPadding = false
      }

      // if(e.target.contentWindow.location.href != this.embedUrl){
      //   this.embedNeedsPadding = false
      // }
      //
      // else{
      //   this.embedNeedsPadding = true
      // }

    },

    embedGoBack(){
      this.nextLoadIsHistoryBack = true
      this.embedHistoryLength--
      window.history.back();
    },

    onHistoryChange(e){
      console.log(e)
    }
  },

  computed: {
    show(){
      if(this.events.length == 0){
        return false
      }
      return true
    },

    schoolId(){
      return this.$store.state.school.id
    },

    username(){
      return this.$store.state.user.username
    }
  }


}
</script>

<style lang="css" scoped>
</style>
