import VueRouter from 'vue-router'

import Events from './pages/Events'
import Grades from './pages/Grades'
import Calculator from './pages/Calculator'
import Bookstore from './pages/Bookstore'
import Contacts from './pages/Contacts'
import Absences from './pages/Absences'

import Subjects from './pages/bookstore/Subjects'
import SellItem from './pages/bookstore/SellItem'
import Item from './pages/bookstore/Item'

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
            path: ':item_id',
            name: 'bookstore.item',
            component: Item
          }
        ]
      },
    ],
});
