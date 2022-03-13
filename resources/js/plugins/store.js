import Vue from 'vue'
import Vuex from 'vuex'

import api from '../business/api'

Vue.use(Vuex)

function hydrate(name, def){
  var value = window.localStorage.getItem(name)
  if(value != undefined){
    if(def != null && typeof def[Symbol.iterator] === 'function' && typeof def != "string" || typeof def == 'object'){
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
  allowNotifications: null,

  user: {},
  schoolClass: {},

  auth: null,
  credentialsToken: null,
  version: null,

  bookstoreSubjects: []
}

var hydratedValues = {}
var setterActions = {}
for(var [key, value] of Object.entries(localStorageValues)){
  var setterKey = JSON.parse(JSON.stringify(key))
  hydratedValues[setterKey] = hydrate(setterKey, value)
  setterActions['set' + setterKey.charAt(0).toUpperCase() + setterKey.slice(1)] = Function("{commit}", "val", "commit('set', ['"+key+"', val])")
}

export default new Vuex.Store({
  state: {
    latestVersion: "1.0.2",
    fetchingData: false,
    ...hydratedValues
  },
  mutations: {
    fetchingData(state){
      state.fetchingData = true
    },
    fetchingStopped(state){
      state.fetchingData = false
    },
    setSchoolSystemFetchInterval(state, data){
      state.schoolSystemFetchInterval = data
    },
    set(state, options){
      var key = options[0]
      var val = options[1]
      state[key] = val
      if(localStorageValues[key] !== undefined){
        var def = localStorageValues[key]
        var originalVal = val
        if(val != null && typeof val[Symbol.iterator] === 'function' && typeof val != "string" || typeof val == 'object'){
          val = JSON.stringify(val)
          def = JSON.stringify(def)
        }

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
    logout({dispatch, commit}){
        dispatch('setUser', {})
        dispatch('setSchoolClass', {})
        dispatch('setCredentialsToken', null)
        dispatch('setSubjects', [])
        dispatch('setGrades', [])
        dispatch('setUpcomingGrades', [])
        dispatch('setAbsencePeriods', [])
        dispatch('setAcceptConditions', false)
        dispatch('setEvents', [])
        dispatch('setBookstoreSubjects', [])
        dispatch('setAuth', null)
        dispatch('setAllowNotifications', null)
        commit('fetchingStopped')
        window.OneSignal.push(() => {
          window.OneSignal.removeExternalUserId();
        });
    },

    startSchoolSystemFetchInterval({state, commit, dispatch, getters}){
      commit('setSchoolSystemFetchInterval', () => {setInterval(() => {
        dispatch("fetchSchoolSystemData")
      }, 120 * 1000)})
    },

    stopSchoolSystemFetchInterval({state, commit, dispatch, getters}){
      clearInterval(state.schoolSystemFetchInterval)
    },

    async fetchSchoolSystemData({state, commit, dispatch, getters}){
        if(state.fetchingData == true){
          return false
        }

        commit('fetchingData')


        if(!state.school){
          commit('fetchingStopped')
          return
        }

        dispatch('setAcceptConditions', true)

        if(!state.credentialsToken){
          commit('fetchingStopped')
          dispatch('logout');
          return
        }

        //User
        var response = await api.fetchSchoolSystemUser()

        if(response.data && getters.schoolSystemLoggedIn){

          dispatch('setUser', response.data)

          //SchoolHub Login
          var userInfo = await api.getUserInfo()
          window.OneSignal.push(() => {
            window.OneSignal.setExternalUserId(userInfo.id);
          });

          _paq.push(['trackGoal', 1]);

          //Subjects
          var response = await api.fetchSchoolSystemSubjects()
          if(response.data && response.data.subjects && getters.schoolSystemLoggedIn){
            dispatch('setSubjects', response.data.subjects)
          }

          //Absences
          var response = await api.fetchSchoolSystemAbsenceInformation()
          if(response.data && response.data.absence_periods && getters.schoolSystemLoggedIn){
            dispatch('setAbsencePeriods', response.data.absence_periods)
          }

          //School Class
          var response = await api.fetchSchoolSystemClass()
          if(response.data && getters.schoolSystemLoggedIn){
            dispatch('setSchoolClass', response.data)
          }

          //Events
          var response = await api.fetchSchoolSystemEvents()
          if(response.data && response.data.events && getters.schoolSystemLoggedIn){
            dispatch('setEvents', response.data.events)
          }
        }
        else{
          dispatch('logout')
        }
        commit('fetchingStopped')
    },

    ...setterActions
  },

  getters: {
    schoolSystemLoggedIn(state){
      return state.credentialsToken != null
    },

    bookstoreAvailable(state){
      return state.school == "gymli"
    },
  }
})
