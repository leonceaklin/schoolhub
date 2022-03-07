<template>
  <v-card class="latest-grades my-5 primary" dark>
    <svg style="display: none">
      <linearGradient id="chart-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
        <stop offset="0%" stop-color="#ffffff" />
        <stop offset="100%" stop-color="#ffffff00" />
      </linearGradient>
    </svg>
    <v-card-title>Neuste</v-card-title>
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
        <!--<span class="text--secondary ml-2">{{ subject.identifier }}</span>!-->
        <span class="ml-2"></span>
<template v-slot:actions>
  <v-chip v-if="subject.grades"
  :class="{'mr-2': true, 'primary': getAvg(subject.grades) >= 5}"
  :color="getAvg(subject.grades) < 4 ? 'red' : ''"
  :dark="getAvg(subject.grades) < 4 ? true : false">∅
    {{ Math.round(getAvg(subject.grades)*100)/100 }}
  </v-chip>
  <v-btn icon @click.stop="subjectsDialog = false; getGradesFromSubject(subject)">
    <v-icon>mdi-plus</v-icon>
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
<p>Eingeloggt als {{ user.name }} ({{ user.username }})</p>
<v-btn class="full-width" @click="logout">Logout</v-btn>
</template>

<script>
export default {
}
</script>

<style lang="css" scoped>
</style>
