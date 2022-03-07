<template>

</template>

<script>
export default {
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
  }
}
</script>

<style lang="css" scoped>
</style>
