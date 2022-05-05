<template>
  <div>
  <page-title>
    {{ $t('calculator.grades_calculator') }}
    <v-spacer/>
    <user-dialog/>
  </page-title>
  <div class="scroll-content">
  <div class="ma-5 mt-0 nav-padding">
    <login-dialog/>
    <v-btn v-if="hasCustomGrades" class="mb-3 full-width primary" :to="{name: 'grades'}">{{ $t('grades.my_grades') }}</v-btn>
    <birthday-banner/>
    <event-tickets/>

    <div class="elevation-2 mb-5">
        <v-data-table class="mb-3" v-if="grades.length > 0" :items="grades" hide-default-footer :mobile-breakpoint="0" :headers="mainGradeHeaders">
          <template v-slot:item.uid="{ item }">
            <v-btn icon @click="removeGrade(item.uid)"><v-icon>mdi-minus</v-icon></v-btn>
          </template>
        </v-data-table>


        <v-col>
          <v-row class="pa-3">
            <v-text-field type="number" ref="valueInput" min="1" :autofocus="$vuetify.breakpoint.width > 1200" max="6" v-model="newGradeValue" :label="$t('calculator.grade_value')" class="mr-2" @keydown.enter="addGrade"></v-text-field>
            <v-text-field @keydown.enter="addGrade" type="number" min="0" max="1" v-model="newGradeWeight" :label="$t('grades.weight')"></v-text-field>
            <v-btn class="ml-2 mt-2" fab color="primary" small @click="addGrade"><v-icon color="white">mdi-plus</v-icon></v-btn>
          </v-row>
        </v-col>
     </div>

    <div class="elevation-2 mb-5 py-3 upcoming-grades" v-if="upcomingGrades.length > 0">
        <h4 class="mx-4">{{ $t('calculator.upcoming_grades_chosen') }}</h4>
        <v-btn icon class="remove-upcoming-grades" @click="upcomingGrades = []"><v-icon>mdi-close</v-icon></v-btn>
        <v-data-table class="mt-3" :items="upcomingGrades" hide-default-footer :mobile-breakpoint="0" :headers="upcomingGradeHeaders">
        </v-data-table>
        <v-col>
          <v-btn class="d-block full-width" color="primary" text @click="setWeightToUpcomingGradesSum"><v-icon class="mr-2">mdi-star-four-points-outline</v-icon>{{ $t('calculator.required_avg') }}</v-btn>
        </v-col>
        <div class="upcoming-grades-description mx-4">
          {{ $t('calculator.required_avg_help') }}
        </div>
    </div>

        <v-text-field type="number" v-model="nextWeight" :label="$t('calculator.next_weight')"></v-text-field>
        <v-slider min="1" max="6" type="number" v-model="aimedAvg" :step="0.5" class="mt-5" thumb-label="always" :label="$t('calculator.aimed_avg')"></v-slider>
        <v-row>

          <v-col v-if="count">
            <p class="text--secondary stat-label">{{ $t('calculator.count') }}</p>
            <h1>{{ count }}</h1>
          </v-col>

          <v-col v-if="avg">
            <p class="text--secondary stat-label">{{ $t('calculator.avg') }}</p>
            <h1>{{ Math.round(avg*100)/100 }}</h1>
          </v-col>

          <v-col v-if="nextGrade">
            <p class="text--secondary stat-label">{{ $t('calculator.required') }}</p>
            <h1 class="rainbow">{{ Math.round(nextGrade*100)/100 }}</h1>
          </v-col>
        </v-row>


        <share/>
  </div>
</div></div>
</template>

<script>
import gradesMixin from "../mixins/grades"
import share from "../components/Share"
import birthdayBanner from "../components/BirthdayBanner"
import loginDialog from "../components/dialogs/Login"
import userDialog from "../components/dialogs/UserInfo"

import eventTickets from "../components/EventTickets"

import pageTitle from "../components/PageTitle"

export default {
  mixins: [gradesMixin],
  components: {
    share,
    birthdayBanner,
    loginDialog,
    userDialog,
    pageTitle,
    eventTickets,
  },
  data(){
    return {
      newGradeValue: null,
      newGradeWeight: 1,

      grades: this.$store.state.grades,
      upcomingGrades: this.$store.state.upcomingGrades,
      nextWeight: this.$store.state.nextWeight,
      aimedAvg: this.$store.state.aimedAvg,


      gradeHeaders: [
        {value: 'name', text: this.$t('grades.name')},
        {value: 'value', text: this.$t('grades.value')},
        {value: 'weight', text: this.$t('grades.weight')},
        {value: 'uid', text: this.$t('calculator.remove')}
      ],

      upcomingGradeHeaders: [
        {value: 'name', text: this.$t('grades.name')},
        {value: 'weight', text: this.$t('grades.weight')},
        {value: 'date', text: this.$t('grades.date')}
      ],
    }
  },

  mounted(){
  },

  methods: {
    addGrade(){
      if(!this.newGradeValue){
        return
      }
      this.grades.push({
        value: this.newGradeValue,
        weight: this.newGradeWeight,
        uid: Math.floor(Math.random()*1000000)
      })

      this.newGradeValue = null
      this.newGradeWeight = 1
      this.$refs.valueInput.focus()
    },
    removeGrade(uid){
      this.grades = this.grades.filter((gr) => {
        return gr.uid != uid
      })
    },
    setWeightToUpcomingGradesSum(){
      var sum = 0
      for(var grade of this.upcomingGrades){
        sum += grade.weight
      }
      this.nextWeight = sum
    },
  },

  computed: {
    avg(){
      return this.sum/this.count
    },
    sum(){
      return this.getSum(this.grades)
    },
    count(){
      return this.getCount(this.grades)
    },
    hasCustomGrades(){
      var subjects = this.$store.state.subjects
      return subjects.length > 0
    },
    mainGradeHeaders(){
      if(this.grades[0].name){
        return this.gradeHeaders
      }
      else{
        return this.gradeHeaders.filter((h) => h.value != "name" && h.value != "date")
      }
    },
    nextGrade(){
      if(this.grades.length == 0 && this.nextWeight && this.aimedAvg){
        return false
      }
      return (((this.aimedAvg*1.0-0.25)*(this.count + this.nextWeight*1.0)) - this.sum*1.0)/this.nextWeight*1.0
    },
    canShowGrades(){
      return this.$store.getters.schoolSystemLoggedIn
    }
  },

  watch: {
    grades:{
      deep: true,
      handler(val){
        this.$store.dispatch('setGrades', val)
      }
    },

    upcomingGrades:{
      deep: true,
      handler(val){
        this.$store.dispatch('setUpcomingGrades', val)
      }
    },

    nextWeight(val){
      this.$store.dispatch('setNextWeight', val)
    },

    aimedAvg(val){
      this.$store.dispatch('setAimedAvg', val)
    }
  }
}
</script>

<style lang="css" scoped>
</style>
