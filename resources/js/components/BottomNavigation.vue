<template>
  <v-bottom-navigation
      v-model="value"
      shift
      color="primary"
    >
      <v-btn :to="{name: 'events'}">
        <span>{{ $t('events.events') }}</span>
        <v-icon>mdi-calendar</v-icon>
      </v-btn>

      <v-btn :to="{name: 'grades'}">
        <span>{{ $t('grades.grades') }}</span>
        <v-icon>mdi-school</v-icon>
      </v-btn>

      <v-btn :to="{name: 'calculator'}">
        <span>{{ $t('calculator.calculator') }}</span>
        <v-icon>mdi-calculator</v-icon>
      </v-btn>

      <v-btn :to="{name: 'absences'}">
        <span>{{ $t('absences.absences') }}</span>
        <v-icon>mdi-clock</v-icon>
      </v-btn>

      <v-btn :to="{name: 'bookstore'}">
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
