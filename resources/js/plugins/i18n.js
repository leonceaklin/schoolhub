import messages from "../vue-i18n-locales.generated.js"
import VueI18n from 'vue-i18n'
import Vue from 'vue'

Vue.use(VueI18n)

export default new VueI18n({
  locale: 'de', // set locale
  messages, // set locale messages
})
