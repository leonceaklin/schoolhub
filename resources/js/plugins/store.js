import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

function hydrate(name, def){
  var value = window.localStorage.getItem(name)
  if(value != undefined){
    if(def != null && typeof def[Symbol.iterator] === 'function'){
      try{
        return JSON.parse(value)
      }catch(e){
        return value
      }
    }
    return value
  }
  return JSON.parse(JSON.stringify(def))
}

var localStorageValues = {
  grades: [],
  aimedAvg: 6,
  upcomingGrades: [],
  nextWeight: 1,
  acceptConditions: false,
  version: null,
  school: null,
  events: [],
  absencePeriods: [],
  subjects: [],

  user: {},
  schoolClass: {},

  auth: null,
  credentialsToken: null,
}

var hydratedValues = []
for(var [key, value] of Object.entries(localStorageValues)){
  hydratedValues[key] = hydrate(key, value)
}

export default new Vuex.Store({
  state: {
    ...hydratedValues
  },
  mutations: {
    set(state, options){
      var key = options[0]
      var val = options[1]
      state[key] = val
      if(localStorageValues[key] !== undefined){
        var def = localStorageValues[key]
        var originalVal = val
        if(val != null && typeof val[Symbol.iterator] === 'function'){
          val = JSON.stringify(val)
          def = JSON.stringify(def)
        }

        console.log(def, val)

        window.localStorage.setItem(key, val)

        if(val == def){
          window.localStorage.removeItem(key)
        }
        if(originalVal != null){
          if(originalVal.length != undefined && def == null && originalVal.length == 0){
            window.localStorage.removeItem(key)
          }
        }
      }
    },
  },
  actions: {
    setGrades({commit}, val) {commit('set', ['grades', val])},
    setAimedAvg({commit}, val) {commit('set', ['aimedAvg', val])},
    setNextWeight({commit}, val) {commit('set', ['nextWeight', val])},
  },

  getters: {
    isLoggedIn(state){
      return state.user != null && state.user != undefined && JSON.stringify(state.user) != "{}"
    }
  }
})
//
//
// lastVersion(val){
//   window.localStorage.setItem("version", val)
// },
//
// upcomingGrades(val){
//   window.localStorage.setItem("upcomingGrades", JSON.stringify(val))
//   if(val.length == 0){
//     window.localStorage.removeItem("upcomingGrades")
//   }
// },
//
// nextWeight(val){
//   window.localStorage.setItem("nextWeight", val)
// },
// school(val){
//   window.localStorage.setItem("school", val)
// },
// subjects(val){
//   window.localStorage.setItem("subjects", JSON.stringify(val))
//   if(val.length == 0){
//     this.fetchSchools()
//     window.localStorage.removeItem("subjects")
//   }
// },
// absencePeriods(val){
//   window.localStorage.setItem("absencePeriods", JSON.stringify(val))
//   if(val.length == 0){
//     window.localStorage.removeItem("absencePeriods")
//   }
// },
// events(val){
//   window.localStorage.setItem("events", JSON.stringify(val))
//   if(val.length == 0){
//     window.localStorage.removeItem("events")
//   }
// },
// user(val){
//   window.localStorage.setItem("user", JSON.stringify(val))
//   if(JSON.stringify(val) == '{}'){
//     window.localStorage.removeItem("user")
//   }
// },
// schoolClass(val){
//   window.localStorage.setItem("schoolClass", JSON.stringify(val))
//   this.calculateBirthday()
//   if(JSON.stringify(val) == '{}'){
//     window.localStorage.removeItem("schoolClass")
//   }
// },
// acceptConditions(val){
//   window.localStorage.setItem("acceptConditions", val)
//   if(!val){
//     window.localStorage.removeItem("acceptConditions")
//   }
// },
// credentialsToken(val){
//   window.localStorage.setItem("credentialsToken", val)
//   if(val == null){
//     window.localStorage.removeItem("credentialsToken")
//   }
// },
//
// auth(val){
//   window.localStorage.setItem("auth", val)
//   if(val == null){
//     window.localStorage.removeItem("auth")
//   }
// },
