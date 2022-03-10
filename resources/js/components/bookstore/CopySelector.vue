<template>
  <div class="copy-selector">
    <div class="mt-2">
      <v-card outlined :class="{'copy-option': true, 'selected': selectedCopy == copy}" @click="selectedCopy = copy" :key="copy.id" v-for="copy in copies">
        <div class="copy-check"><v-icon>{{ selectedCopy == copy ? 'mdi-check-circle' : 'mdi-circle-outline' }}</v-icon></div>
        <div class="copy-condition">
          <b>{{ getCondition(copy) }}</b>
          <div v-if="copy.edition">{{copy.edition.number}}. Auflage ({{copy.edition.year}}) {{ copy.edition.name }}</div>
        </div>
        <div class="copy-price">CHF {{ copy.price }}.-</div>
      </v-card>
    </div>
  </div>
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
      this.$router.replace({params: {copy_uid: val.uid}})
    },
    copies(){
      this.checkSelectedCopy()
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
    },

    checkSelectedCopy(){
      if(this.$route.params.copy_uid){
        var selectedCopyAvailable = false
        for(var copy of this.copies){
          if(copy.uid == this.$route.params.copy_uid){
            this.selectedCopy = copy
            selectedCopyAvailable = true
          }
        }
        if(!selectedCopyAvailable){
          this.$router.replace({params: {copy_uid: undefined}})
        }
      }
    }
  },

  async mounted(){
    this.selectedCopy = this.copies[0]
    this.checkSelectedCopy()
  }
}
</script>

<style lang="css" scoped>
</style>
