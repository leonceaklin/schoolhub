<template>

</template>

<script>
export default {
  props: ['copies'],
  data(){
    return {
      selectedCopy: null,
      conditions: ["Neuwertig", "Kleine Gebrauchsspuren", "Entfernbare Notizen", "Permanente Notizen", "Starke Gebrauchsspuren"]
    }
  },

  computed: {

  },

  watch: {
    selectedCopy(val){
      this.$emit('select', val)
    }
  },

  methods: {
    getCondition(copy){
      if(!copy.condition){
        return "Zustand unbekannt"
      }
      else{
        return this.conditions[copy.condition-1]
      }
    }
  },

  async mounted(){
    this.selectedCopy = this.copies[0]
  }
}
</script>

<style lang="css" scoped>
</style>
