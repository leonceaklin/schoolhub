import  VueMatomo from 'vue-matomo'
import Vue from 'vue'

import router from "../router.js"

Vue.use(VueMatomo, {
  // Configure your matomo server and site by providing
  host: 'https://analytics.zebrapig.com',
  siteId: 102,
  router: router
})
