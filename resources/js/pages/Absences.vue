<template>
  <div>
    <page-title>
      {{ $t('absences.absences') }}
      <v-spacer/>
      <user-dialog/>
    </page-title>
    <div class="scroll-content">
    <div class="mx-5 nav-padding">
    <div v-for="period in absencePeriods">
      <v-card class="primary pa-3 mb-5 rounded-lg" dark v-if="period.initial_quota && period.remaining_quota">
          <b>{{ $t('absences.quota') }}</b>
          <svg class="quota-chart" width="100%" height="100%" viewBox="0 0 100 90">
            <circle cx="50" cy="50" r="47" class="background"/>
            <circle cx="50" cy="50" r="47" class="foreground" :style="{'--final-dashoffset': 609.5 - (205.5 * period.remaining_quota/period.initial_quota)}"/>
            <text text-anchor="middle" style="font-weight: bold; font-size: 50" fill="#ffffff" alignment-baseline="central">
              <tspan x="50" dy="60">{{ period.remaining_quota }}</tspan>
            </text>
            <text text-anchor="middle" style="font-size: 10; opacity: 0.5" fill="#ffffff" alignment-baseline="central">
              <tspan x="50" dy="80">/ {{ period.initial_quota }}</tspan>
            </text>
          </svg>
          <div class="quota-chart-period">
            {{ (new Date(period.start)).toLocaleDateString("de-DE") }} -
            {{ (new Date(period.end)).toLocaleDateString("de-DE") }}
          </div>
      </v-card>
      <v-data-table v-if="period.absences" :items="period.absences" hide-default-footer :mobile-breakpoint="0" :headers="absenceHeaders">
        <template v-slot:item.start="{ item }">
          {{ (new Date(item.start)).toLocaleDateString("de-DE", {weekday: 'short', day: 'numeric', month: 'numeric', year:'numeric'}) }}
        </template>
      </v-data-table>
    </div>
  </div>
</div>
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
      absenceHeaders: [
        {value: 'start', text: this.$t('absences.date')},
        {value: 'reason', text: this.$t('absences.reason')},
        {value: 'points', text: this.$t('absences.points')}
      ],
    }
  },
  mounted(){
    _paq.push(['trackGoal', 3]);
  },
  computed: {
    absencePeriods(){
      return this.$store.state.absencePeriods
    }
  }
}
</script>

<style lang="css" scoped>
</style>
