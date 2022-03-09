<template>
  <v-tabs v-model="eventsTab" grow>
    <v-tab>{{ $t('events.overview') }}</v-tab>
    <v-tab>{{ $t('events.calendar') }}</v-tab>
  </v-tabs>
  <v-divider></v-divider>
  <v-card-text class="pa-0">
  <v-tabs-items v-model="eventsTab" style="height: 100%; overflow: auto;">
  <v-tab-item>
    <div class="pa-5">
    <v-card class="elevation-2 pa-4 mb-4" v-if="nextEvent">
      <b>Als nächstes</b>
      <p class="mt-2 mb-0">{{ (new Date(nextEvent.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }}</p>
      <h1 class="mb-2 mt-0">{{ subjects.filter((s) => {return s.identifier == nextEvent.title})[0] ? subjects.filter((s) => {return s.identifier == nextEvent.title})[0].name : nextEvent.title}}</h1>
      <p>{{ nextEvent.title }}</p>
      <h3 v-if="upcomingEvents[0].room"><v-icon>mdi-map-marker</v-icon>{{ upcomingEvents[0].room.label }}</h3>
    </v-card>
    <div class="mb-5 mt-2">
      <b>Nächste Prüfungen</b>
      <v-card class="primary rounded-lg pa-4 my-2"dark v-for="event in tests.slice(0, 4)">
        {{ (new Date(event.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }} - <b>{{ event.title }}</b>
        <h2>{{ event.grade.name }}</h2>
        <p v-if="event.grade.description" v-html="event.grade.description.replaceAll('?', '')"></p>
        <p class="mb-0">{{ relativeTimeDays(event.start.replaceAll("-", "/"), "de-DE") }}</p>
      </v-card>
    </div>

    <div class="my-5">
      <b>Veränderungen im Stundenplan</b>
      <v-card class="rounded-lg pa-4 my-2" :color="event.deleted ? 'red' : 'orange'" dark v-for="event in alteredEvents.slice(0, 5)">
        <v-row>
          <v-col style="flex-grow: 0.05">
            <v-icon v-if="event.substitute">mdi-account-switch</v-icon>
            <v-icon v-else-if="event.moved">mdi-pencil</v-icon>
            <v-icon v-else-if="event.no_teacher">mdi-clipboard-account</v-icon>
            <v-icon v-else-if="event.deleted">mdi-trash</v-icon>

          </v-col>
          <v-col>
            {{ (new Date(event.start.replaceAll("-", "/"))).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric', hour:'numeric', minute: 'numeric'}) }} - <b>{{ event.title }}</b>
            <v-row>
              <v-col v-if="event.no_teacher">Arbeitsauftrag von {{ event.teacher.name }}</v-col>
              <v-col v-if="event.substitute"><b>{{ event.teacher.identifier }}</b> als Stellvertretung</v-col>
              <v-col v-if="event.moved">Lektion verschoben oder verändert</v-col>
              <v-col v-if="event.deleted">Fällt aus</v-col>
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
          <v-select class="d-inline-block calendar-select" label="Anzeigen" dense v-model="eventsCalendarMode" :items="eventsCalendarModes"></v-select>
          <h2 class="ml-4 mt-2">{{ (new Date(eventsCalendarDate)).toLocaleDateString("de-DE", {month: 'long'}) }}</h2>
          <v-spacer></v-spacer>
          <v-btn @click="$refs.eventsCalendar.prev()" icon><v-icon>mdi-chevron-left</v-icon></v-btn>
          <v-btn text @click="eventsCalendarDate = new Date()" dense>Heute</v-btn>
          <v-btn @click="$refs.eventsCalendar.next()" icon><v-icon>mdi-chevron-right</v-icon></v-btn>
      </div>

      <v-calendar locale="de-DE" ref="eventsCalendar" class="calendar" v-model="eventsCalendarDate"
      :type="eventsCalendarMode" :weekdays="[1, 2, 3, 4, 5]" :now="new Date()"
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
</template>

<script>
export default {
}
</script>

<style lang="scss" scoped>
</style>
