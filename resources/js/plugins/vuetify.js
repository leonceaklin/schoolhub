import Vuetify from "vuetify"
import Vue from 'vue'

Vue.use(Vuetify)

var themeColor = "#308efc"

export default new Vuetify({
  theme: {
    themes: {
      light: {
        primary: themeColor
      },
      dark: {
        primary: themeColor
      }
    }
  }
})
