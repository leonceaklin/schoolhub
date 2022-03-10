<template>
  <v-bottom-navigation
      shift
      class="bottom-navigation elevation-0"
      color="primary"
    >
      <v-btn :to="{name: 'events'}" :ripple="false" :disabled="$store.state.events.length == 0">
        <span>{{ $t('events.events') }}</span>
        <v-icon>mdi-calendar</v-icon>
      </v-btn>

      <v-btn :to="{name: 'grades'}" :ripple="false" :disabled="$store.state.subjects.length == 0">
        <span>{{ $t('grades.grades') }}</span>
        <v-icon>mdi-school</v-icon>
      </v-btn>

      <v-btn :to="{name: 'calculator'}" :ripple="false" :exact-path="true">
        <span>{{ $t('calculator.calculator') }}</span>
        <v-icon>mdi-calculator</v-icon>
      </v-btn>

      <v-btn :to="{name: 'absences'}" :ripple="false" :disabled="$store.state.absencePeriods.length == 0">
        <span>{{ $t('absences.absences') }}</span>
        <v-icon>mdi-clock</v-icon>
      </v-btn>

      <v-btn :to="{name: 'bookstore'}" :ripple="false">
        <span>{{ $t('bookstore.bookstore') }}</span>
        <v-icon>mdi-book</v-icon>
      </v-btn>
    </v-bottom-navigation>
</template>

<script>
export default {
}
</script>

<style lang="css" scoped>
</style>
