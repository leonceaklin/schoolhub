import VueRouter from 'vue-router'

import Events from './pages/Events'
import Grades from './pages/Grades'
import Calculator from './pages/Calculator'
import Bookstore from './pages/Bookstore'
import Contacts from './pages/Contacts'
import Absences from './pages/Absences'

export default new VueRouter({
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
        path: '/calculator',
        name: 'calculator',
        component: Calculator,
      },
      {
        path: '/bookstore',
        name: 'bookstore',
        component: Bookstore,
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
      }
    ],
});
