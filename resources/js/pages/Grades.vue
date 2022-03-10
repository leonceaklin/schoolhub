<template>
  <div>
    <page-title>
      {{ $t('grades.my_grades') }}
      <v-spacer/>
      <user-dialog/>
    </page-title>
    <div class="scroll-content">
  <div class="ma-5 mt-0 nav-padding">
  <v-card class="latest-grades mb-5 primary" dark>
    <svg style="display: none">
      <linearGradient id="chart-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
        <stop offset="0%" stop-color="#ffffff" />
        <stop offset="100%" stop-color="#ffffff00" />
      </linearGradient>
    </svg>
    <v-card-title>{{ $t('grades.latest') }}</v-card-title>
    <div class="pa-5 pt-0">
      <div ripple v-for="(grade, key) in gradesSortedByDate.slice(0,4)">
        <v-row class="my-1">
          <v-col style="flex-grow:1.5">
            {{ grade.subject.name }}
            <!--<v-btn small icon @click.stop="subjectsDialog = false; getGradesFromSubject(grade.subject)">
        <v-icon>mdi-plus</v-icon>
      </v-btn>!-->
          </v-col>
          <v-col style="flex-grow:2" class="px-0">{{ grade.name }}</v-col>
          <v-col class="text-right" style="flex-grow:0.5">
            <h3>{{ grade.value }}</h3>
          </v-col>
        </v-row>
        <v-divider v-if="key < 3"></v-divider>
      </div>
    </div>
  </v-card>
  Es kann selten vorkommen, dass einige Fächer fehlen. Wir arbeiten daran.
  <v-expansion-panels class="select-subject mb-5">
    <v-expansion-panel v-for="subject in subjects">
      <v-expansion-panel-header disable-icon-rotate>
        {{ subject.name }}
        <span class="ml-2"></span>
<template v-slot:actions>
  <v-chip v-if="subject.grades"
  :class="{'mr-2': true, 'primary': getAvg(subject.grades) >= 5}"
  :color="getAvg(subject.grades) < 4 ? 'red' : ''"
  :dark="getAvg(subject.grades) < 4 ? true : false">∅
    {{ Math.round(getAvg(subject.grades)*100)/100 }}
  </v-chip>
  <v-btn icon @click.stop="subjectsDialog = false; getGradesFromSubject(subject)">
    <v-icon>mdi-calculator</v-icon>
  </v-btn>
</template>
</v-expansion-panel-header>
<v-expansion-panel-content>
  <v-data-table v-if="subject.grades" :items="subject.grades" hide-default-footer :mobile-breakpoint="0" :headers="subjectGradeHeaders">
  </v-data-table>
  <v-card class="primary pa-3 mt-5 mb-2 rounded-lg" dark v-if="subject.grades">
    <b>Verlauf</b>
    <g-chart
      class="grade-chart overflow-hidden"
      type="AreaChart"
      @ready="onChartReady"
      :options="chartOptions"
      :data="getChartData(subject.grades)"
    />
  </v-card>
</v-expansion-panel-content>
</v-expansion-panel>
</v-expansion-panels>
</div></div></div>
</template>

<script>
import { GChart } from 'vue-google-charts'
import gradesMixin from '../mixins/grades'

import pageTitle from "../components/PageTitle"
import userDialog from "../components/dialogs/UserInfo"

export default {
  mixins: [gradesMixin],
  components: {GChart, pageTitle, userDialog},
  data(){
    return {
      chartOptions: {
        chartArea:{left:25,top:10,bottom:10,right:10},
        vAxis: {minValue: 0, maxValue: 6, ticks: [1,2,3,4,5,6]},
        hAxis: {textPosition: 'none'},
        legend: {position: 'none'},
        pointSize: 5,
        series: {
          0: {
            color: "#ffffff"
          }
        },
        trendlines: {
          0: {
            type: 'polynomial',
            degree: 3,
            visibleInLegend: true,
          }
        },
        animation: {
          startup: true,
          duration: 500,
          easing: 'out',
        },
      },

      subjectGradeHeaders: [
        {value: 'name', text: this.$t('grades.name')},
        {value: 'value', text: this.$t('grades.value')},
        {value: 'weight', text: this.$t('grades.weight')},
      ],
    }
  },

  computed: {
    subjects(){
      return this.$store.state.subjects
    },
    user(){
      return this.$store.state.user
    },
    gradesSortedByDate(){
      var grades = []
      for(var subject of this.subjects){
        if(subject.grades){
          for(var grade of subject.grades){
            if(grade.value && grade.date){
              var g = {
                subject: subject,
                ... grade
              }
              grades.push(g)
            }
          }
        }
      }
      grades = grades.sort((a, b) => {
        if(new Date(a.date) > new Date(b.date)){
          return -1
        }
        else if(new Date(a.date) < new Date(b.date)){
          return 1
        }
        return 0
      })

      return grades
    },
  },

  methods: {
    onChartReady(chart){
      let observer = new MutationObserver((e) => {
        var svg = chart.container.getElementsByTagName('svg')[0]
        if(svg){
          var checkGradient = svg.getElementById("chart-gradient")
          if(checkGradient != undefined){
            return;
          }

          var gradient = document.getElementById("chart-gradient").cloneNode(true)
          svg.prepend(gradient)
          var path = svg.getElementsByTagName('path')[0]
          if(!path) return
          path.setAttribute('fill', 'url(#chart-gradient) #ffffff')
        }
      })

      observer.observe(chart.container, {childList: true, subtree: true})
    },

    getChartData(grades){
      var chartData = [
        [this.$t('grades.name'), this.$t('grades.value')]
      ]
      for(var grade of grades){
        if(grade.value){
          chartData.push([
            grade.name, grade.value
          ])
        }
      }

      return chartData
    },

    getGradesFromSubject(subject){
      var grades = []
      var upcomingGrades = []
      for(var grade of subject.grades){
        var date = new Date(grade.date)
        if(grade.value && grade.weight){
          grades.push({
            date: date.toLocaleDateString("de-DE"),
            name: grade.name,
            value: grade.value,
            weight: grade.weight,
            uid: Math.floor(Math.random()*1000000)
          })
        }
        else if(grade.weight && date > new Date()){
          upcomingGrades.push({
            date: date.toLocaleDateString("de-DE"),
            name: grade.name,
            value: grade.value,
            weight: grade.weight,
            uid: Math.floor(Math.random()*1000000)
          })
        }
      }

      this.$store.dispatch('setGrades', grades)
      this.$store.dispatch('setUpcomingGrades', upcomingGrades)
      this.$router.push({name: 'calculator'})
    },
  }
}
</script>

<style lang="css" scoped>
</style>
