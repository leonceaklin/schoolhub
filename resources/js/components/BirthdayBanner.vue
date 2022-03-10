<template lang="html">
  <div>
    <transition name="fade">
      <div v-if="this.birthdayToday.length > 0">
        <v-card class="pa-4 mb-4 birthday-card" v-for="birthdayChild in this.birthdayToday">
          <v-icon class="mr-2">mdi-cake-variant</v-icon>
          <b v-if="birthdayChild.first_name != user.first_name || birthdayChild.last_name != user.last_name">
            {{ birthdayChild.first_name.split(" ")[0] }} wird heute {{ birthdayChild.age }}!
          </b>

          <b v-if="birthdayChild.birth == user.birth && birthdayChild.first_name == user.first_name && birthdayChild.last_name == user.last_name">
            Happy Birthday!
          </b>
        </v-card>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  data(){
    return {
      birthdayToday: [],
    }
  },
  computed: {
    schoolClass(){
      return this.$store.state.schoolClass
    },
    user(){
      return this.$store.state.user
    }
  },

  methods: {
    calculateBirthday(){
      if(this.schoolClass == null){
        this.birthdayToday = [];
        return false;
      }

      var students = this.schoolClass.students
      var today = new Date();

      var birthdayToday = [];

      if(!students){
        this.birthdayToday = [];
        return false;
      }

      for(var student of students){
        var birth = new Date(student.birth.replaceAll("-", "/"))
        if(birth.getDate() == today.getDate() && birth.getMonth() == today.getMonth()){
          var age = today.getYear() - birth.getYear()
          birthdayToday.push({age: age, ...student})
        }
      }

      this.birthdayToday = birthdayToday
    },
  },

  mounted(){
    this.calculateBirthday()
  }
}
</script>

<style lang="css" scoped>
</style>
