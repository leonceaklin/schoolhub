import VueRouter from 'vue-router'

import store from "./plugins/store.js"

import Events from './pages/Events'
import Grades from './pages/Grades'
import Calculator from './pages/Calculator'
import Bookstore from './pages/Bookstore'
import Contacts from './pages/Contacts'
import Absences from './pages/Absences'

import Subjects from './pages/bookstore/Subjects'
import SellItem from './pages/bookstore/SellItem'
import Item from './pages/bookstore/Item'
import CancelOrder from './pages/bookstore/CancelOrder'

const router = new VueRouter({
    mode: 'history',
    routes: [
      {
        path: '/events',
        name: 'events',
        component: Events,
      },
      {
        path: '/grades',
        name: 'grades',
        component: Grades,
      },
      {
        path: '/',
        name: 'calculator',
        component: Calculator,
      },
      {
        path: '/contacts',
        name: 'contacts',
        component: Contacts,
      },
      {
        path: '/absences',
        name: 'absences',
        component: Absences,
      },
      {
        path: '/bookstore',
        component: Bookstore,
        children: [
          {
            path: '',
            name: 'bookstore',
            component: Subjects
          },
          {
            path: 'sell',
            name: 'bookstore.sell',
            component: SellItem
          },
          {
            path: 'cancel/:order_hash',
            name: 'bookstore.cancelorder',
            component: CancelOrder
          },
          {
            path: ':item_id/:copy_uid?',
            name: 'bookstore.item',
            component: Item
          }
        ]
      },
    ],
});

router.beforeEach(async (to, from, next) => {
  if(to.name == "grades"){
    if(store.state.subjects.length == 0){
      next({name: "calculator"})
    }
  }

  if(to.name == "absences"){
    if(store.state.absencePeriods.length == 0){
      next({name: "calculator"})
    }
  }

  if(to.name == "events"){
    if(store.state.events.length == 0){
      next({name: "calculator"})
    }
  }

  if(to.name == "contacts"){
    if(store.state.class.name == undefined){
      next({name: "calculator"})
    }
  }

  next()
})

router.afterEach(async (to, from) => {
  _paq.push(['trackPageView']);
})

export default router
