<template>
  <div class="events-content">
    <page-title no-fix>
      {{ $t('events.events') }}
      <v-spacer/>
      <user-dialog/>
    </page-title>
  <v-tabs v-model="eventsTab" grow>
    <v-tab>{{ $t('events.overview') }}</v-tab>
    <v-tab>{{ $t('events.calendar') }}</v-tab>
  </v-tabs>
  <v-divider></v-divider>
  <v-tabs-items v-model="eventsTab" style="height: 100%; overflow: auto;">
  <v-tab-item>
    <div class="pa-5 nav-padding-bottom">
    <v-card class="elevation-2 pa-4 mb-4" v-if="nextEvent">
      <b>{{ $t('events.upcoming') }}</b>
      <p class="mt-2 mb-0">{{ (new Date(nextEvent.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }}</p>
      <h1 class="mb-2 mt-0">{{ subjects.filter((s) => {return s.identifier == nextEvent.title})[0] ? subjects.filter((s) => {return s.identifier == nextEvent.title})[0].name : nextEvent.title}}</h1>
      <p>{{ nextEvent.title }}</p>
      <h3 v-if="upcomingEvents[0].room"><v-icon>mdi-map-marker</v-icon>{{ upcomingEvents[0].room.label }}</h3>
    </v-card>
    <div class="mb-5 mt-2">
      <b>{{ $t('events.upcoming_tests') }}</b>
      <v-card class="primary rounded-lg pa-4 my-2"dark v-for="event in tests.slice(0, 4)">
        {{ (new Date(event.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }} - <b>{{ event.title }}</b>
        <h2>{{ event.grade.name }}</h2>
        <p v-if="event.grade.description" v-html="event.grade.description.replaceAll('?', '')"></p>
        <p class="mb-0">{{ relativeTimeDays(event.start.replaceAll("-", "/"), "de-DE") }}</p>
      </v-card>
    </div>

    <div class="my-5">
      <b>{{ $t('events.schedule_changes') }}</b>
      <v-card class="rounded-lg pa-4 my-2" :color="event.deleted ? 'red' : 'orange'" dark v-for="event in alteredEvents.slice(0, 5)">
        <v-row>
          <v-col style="flex-grow: 0.05">
            <v-icon v-if="event.substitute">mdi-account-switch</v-icon>
            <v-icon v-else-if="event.moved">mdi-pencil</v-icon>
            <v-icon v-else-if="event.no_teacher">mdi-clipboard-account</v-icon>
            <v-icon v-else-if="event.deleted">mdi-delete</v-icon>

          </v-col>
          <v-col>
            {{ (new Date(event.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }} - <b>{{ event.title }}</b>
            <v-row>
              <v-col v-if="event.no_teacher">{{ $t('events.task_by', {name: event.teacher.name}) }}</v-col>
              <v-col v-if="event.substitute"><b>{{ event.teacher.identifier }}</b> als Stellvertretung</v-col>
              <v-col v-if="event.moved">{{ $t('events.lession_changed') }}</v-col>
              <v-col v-if="event.deleted">{{ $t('events.cancelled') }}</v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-card>
    </div>
    </div>
  </v-tab-item>
  <v-tab-item style="height: 100%">
    <div v-if="calendarEvents" class="calendar-wrapper">
      <div class="d-flex calendar-controls pa-4 pb-0">
        <!--<v-btn-toggle v-model="eventsCalendarMode" group mandatory>
          <v-btn :value="mode.value" v-for="mode in eventsCalendarModes" small>{{mode.text}}</v-btn>
        </v-btn-toggle>!-->
          <v-select class="d-inline-block calendar-select" :label="$t('events.show')" dense v-model="eventsCalendarMode" :items="eventsCalendarModes"></v-select>
          <h2 class="ml-4 mt-2">{{ (new Date(eventsCalendar)).toLocaleDateString("de-DE", {month: 'long'}) }}</h2>
          <v-spacer></v-spacer>
          <v-btn @click="$refs.eventsCalendar.prev()" icon><v-icon>mdi-chevron-left</v-icon></v-btn>
          <v-btn text @click="eventsCalendar = new Date()" dense>{{ $t('events.today') }}</v-btn>
          <v-btn @click="$refs.eventsCalendar.next()" icon><v-icon>mdi-chevron-right</v-icon></v-btn>
      </div>

      <v-calendar locale="de-DE" ref="eventsCalendar" class="calendar" v-model="eventsCalendar"
      :type="eventsCalendarMode" :weekdays="[1, 2, 3, 4, 5]" :now="now"
      :events="calendarEvents" first-time="07:00" @click:event="onEventClick"
      event-overlap-mode="column">
         <template v-slot:day-body="{ date, week }">
      <div
        class="v-current-time"
        :class="{ first: date === week[0].date }"
        :style="{ top: eventsCalendarNowY }"
      ></div>
    </template>
      </v-calendar>
    </div>
  </v-tab-item>
  </v-tabs-items>

  <v-dialog transition="dialog-bottom-transition" v-model="eventDetailDialog" width="300">
    <v-card v-if="focusedEvent" :class="{'event-detail': true, primary: focusedEvent.type == 'test', 'orange': focusedEvent.altered, 'red': focusedEvent.deleted}" :dark="focusedEvent.altered || focusedEvent.type == 'test'">
      <v-card-title><v-btn icon class="mr-2" @click="eventDetailDialog = false"><v-icon>mdi-close</v-icon></v-btn>{{ focusedEvent.type == "test" ? focusedEvent.grade.name : focusedEvent.title }}</v-card-title>
      <v-card-text>
        <p>
        {{ (new Date(focusedEvent.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }}
        </p>
        <p v-if="focusedEvent.deleted">Fällt aus</p>
        <p v-if="focusedEvent.room && focusedEvent.room.key != 0">
          <v-icon>mdi-map-marker</v-icon>
          {{ focusedEvent.room.label }}
        </p>
        <p v-if="focusedEvent.substitute"><b>{{ focusedEvent.teacher.identifier }}</b> als Stellvertretung</p>
        <p v-if="focusedEvent.grade">
          {{ focusedEvent.grade.description }}
        </p>

      </v-card-text>
    </v-card>
  </v-dialog>

</div>
</template>

<script>
import pageTitle from "../components/PageTitle"
import userDialog from "../components/dialogs/UserInfo"

export default {
  components: {
    pageTitle,
    userDialog
  },
  data(){
    return {
      eventsCalendarMode: "day",

      now: (new Date),
      nowString: null,

      eventDetailDialog: false,
      focusedEvent: null,

      eventsCalendarModes: [
        {text: this.$t('events.day'), value: "day"},
        {text: this.$t('events.week'), value: "week"},
        {text: this.$t('events.month'), value: "month"},
      ],

      eventsCalendar: new Date(),
      eventsTab: 0,
      timesInterval: null,

      subjects: this.$store.state.subjects,
    }
  },
  mounted(){
    this.setNow()
    this.eventsCalendarDate = this.formatDateString(new Date())
    _paq.push(['trackGoal', 2]);

    this.timesInterval = setInterval(() => {
      console.log(this.$refs.eventsCalendar)
      this.setNow()
      this.$refs.eventsCalendar.updateTimes()
    }, 60 * 1000)
  },

  beforeDestroy(){
    clearInterval(this.timesInterval)
  },

  methods: {
    setNow(){
      var date = new Date()
      this.now = date
      var nowString = this.formatDateString(date)
      this.nowString = nowString
    },
    formatDateString(date){
      var now = ""
      now += date.getFullYear() + "-"
      now += date.getMonth()+1 > 9 ? '' : '0'
      now += date.getMonth()+1 + "-"

      now += date.getDate() > 9 ? '' : '0'
      now += date.getDate() + " "

      now += date.getHours() > 9 ? '' : '0'
      now += date.getHours() + ":"
      now += date.getMinutes() > 9 ? '' : '0'
      now += date.getMinutes()
      console.log(now)
      return now
    },
    relativeTimeDays(value, locale) {
      const date = new Date(value);
      const deltaDays = (date.getTime() - Date.now()) / (1000 * 3600 * 24);
      const formatter = new Intl.RelativeTimeFormat(locale);
      var days = Math.round(deltaDays);
      if(days != 0){
        return formatter.format(days, 'days');
      }
      return "Heute";
    },
    onEventClick(e){
      this.focusedEvent = e.event.event
      this.eventDetailDialog = true
    },
  },
  computed: {
    events(){
      return this.$store.state.events
    },
    eventsCalendarNowY(){
      if(!this.$refs.eventsCalendar){
        return "-10px"
      }
      return this.$refs.eventsCalendar.timeToY(this.$refs.eventsCalendar.times.now) + 'px'
    },
    upcomingEvents(){
      var now = new Date()
      var filtered = this.events.filter((e) => {
        return new Date(e.start.replaceAll("-", "/")) >= now
      })

      return filtered.sort((a,b) => {
        if(new Date(a.start.replaceAll("-", "/")) > new Date(b.start.replaceAll("-", "/"))){
          return 1
        }

        if(new Date(a.start.replaceAll("-", "/")) < new Date(b.start.replaceAll("-", "/"))){
          return -1
        }

        return 0
      })
    },

    nextEvent(){
      return this.upcomingEvents.filter((e) => {
        return (e.type != "appointment" && !e.deleted)
      })[0]
    },
    alteredEvents(){
      return this.upcomingEvents.filter((e) => e.altered)
    },
    calendarEvents(){
      var events = this.events
      var calendarEvents = []
      for(var event of events){
        var calendarEvent = {
          start: event.start,
          end: event.end
        }

        calendarEvent.color = "grey lighten-1"

        var eventName = ""
        var subject = this.subjects.find((s) => {return s.identifier == event.title})
        if(subject){
          eventName = subject.name
        }
        else{
          eventName = event.title
        }

        if(event.type == "test"){
          calendarEvent.color = "primary"
          eventName = event.grade.name
        }

        if(event.room && event.room.key != 0){
          eventName += " – "+event.room.label;
        }
        calendarEvent.name = eventName;


        if(event.altered){
          calendarEvent.color = "orange"
        }

        if(event.deleted){
          calendarEvent.color = "red"
        }

        calendarEvent.event = event

        var dateDiff = new Date(event.end.replaceAll("-", "/"))
         - new Date(event.start.replaceAll("-", "/"));

        if(dateDiff < 360000 * 24 && dateDiff > 0){
          calendarEvents.push(calendarEvent)
        }


      }
      return calendarEvents
    },
    tests(){
      return this.upcomingEvents.filter((e) => e.type == "test")
    },
  }
}
</script>

<style lang="scss" scoped>
</style>
