export default {
  methods: {
    getCount(grades){
      var count = 0
      for(var grade of grades){
        if(grade.value){
          count+=(1.0*grade.weight)
        }
      }
      return count
    },
    getAvg(grades){
      return this.getSum(grades)/this.getCount(grades)
    },
    getSum(grades){
      var sum = 0
      for(var grade of grades){
        if(grade.value){
          sum+=(grade.value*grade.weight)
        }
      }
      return sum
    },
  }
}
