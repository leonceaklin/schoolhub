<template>
  <div>
    <div class="my-2">
    <v-btn icon class="mr-2 mt-2" :to="{name: 'bookstore'}">
      <v-icon>mdi-arrow-left</v-icon>
    </v-btn>
    <img v-if="!item" src="images/sell.svg" class="sell-icon">
    <h2 class="center">Verkaufe ein Buch</h2>

      <div v-if="!this.item">
        <p class="center">Scanne als erstes den Barcode deines Buchs, um herauszufinden, ob wir es verkaufen können.</p>
          <v-btn class="primary full-width" v-if="!showScanner" @click="showScanner = true; showManually = false"><v-icon dark left>mdi-barcode-scan</v-icon>Barcode scannen</v-btn>
          <barcode-scanner v-if="showScanner" @scan="checkIsbn" @error="showScanner = false; showManually = true"></barcode-scanner>

          <v-btn class="full-width mt-3" v-if="!this.showManually" @click="showManually = true; showScanner = false">ISBN manuell eingeben</v-btn>
          <v-text-field autofocus @keydown="checkIsbnInput" v-if="showManually" type="text" hint="Lass die Bindestriche weg. Gib nur die Zahlen ein." v-model="isbnInput" class="mt-4" outlined label="ISBN" pattern="\d*"></v-text-field>

          <div v-if="notFound" class="text--secondary">Wir haben nichts gefunden. Du kannst eine Aufnahme des Buchs beantragen:<br>
              <v-btn class="btn primary mt-2 full-width" :href="'mailto:GymLi Bookstore Support<'+'buecher'+'gymli'+'estal'+'@'+'gma'+'il.c'+'om?subject=['+isbn+'] Bitte nehmt dieses Buch in den Bookstore auf&body=ISBN: '+isbn+'%0D%0AMein Name: '+user.name+' ('+user.username+')%0D%0ADas Buch heisst: %0D%0AWird in folgendem Fach verwendet: %0D%0A'"><v-icon dark left>mdi-book</v-icon>Aufnahme beantragen</v-btn>
          </div>
      </div>
      <div class="mt-3" v-if="this.item">
        <v-img class="full-cover small elevation-2 mt-2" :aspect-ratio="item.cover.width/item.cover.height" :src="item.cover.data.thumbnails[5].url"></v-img>
        <h2 class="item-title">{{ item.title }}</h2>
        <h3 class="item-authors">{{ item.authors }}</h3>

        <v-text-field @blur="validatePrice" @keydown="checkPriceInput" class="mt-5" :error="priceError" pattern="\d*" prefix="CHF" type="number" v-model="price" class="mt-2" outlined autofocus :min="1" :max="200" label="Preis" :hint="priceHint"></v-text-field>

        <v-select v-model="edition" :items="editions" v-if="editions.length > 0" outlined label="Auflage"></v-select>

        <v-btn @click="goToConfirmation" :loading="verifyingAccount" v-if="this.price" class="primary full-width mt-2" :disabled="priceError || !editionValid">Für CHF {{ price }}.- verkaufen</v-btn>
      </div>

    <v-dialog
        v-model="confirmSaleVisible"
        scrollable
        transition="dialog-bottom-transition"
        width="500">
      <v-card>
          <v-card-title>
            <v-btn icon class="mr-2" @click="confirmSaleVisible = false"><v-icon>mdi-close</v-icon></v-btn>
            Verkauf bestätigen
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <div v-if="!confirmed">
              <div v-if="item" class="mt-5 text-main">
              <v-img class="full-cover small elevation-2 mt-2" :aspect-ratio="item.cover.width/item.cover.height" :src="item.cover.data.thumbnails[5].url"></v-img>
              <h2 class="item-title">{{ item.title }}</h2>
              <h3 class="item-authors mb-3">wird verkauft für CHF {{ price }}.-</h3>
              <h3 class="item-authors mt-6">Du erhältst beim Verkauf von uns:</h3>
              <h2 class="copy-price-large mt-2">CHF {{ payback }}</h2>
              </div>

              <v-expansion-panels v-model="expansionPanels">
              <v-expansion-panel>
                  <v-expansion-panel-header>
                    <div>
                      <v-icon class="mr-2">mdi-account</v-icon> Verkaufen als {{ user.first_name }} {{ user.last_name }}
                    </div>
                  </v-expansion-panel-header>
                  <v-expansion-panel-content>
                    <p>Überprüfe deine Kontaktdaten, damit wir dir das Geld nach dem Verkauf auszahlen können. Sie gelten für alle Bücher, die du momentan verkaufst.</p>
                    <v-text-field type="email" :error="!emailValid" label="E-Mail" hint="Du kannst keine Schul-E-Mail angeben, da du diese beim Austritt verlieren wirst" outlined v-model="user.email"></v-text-field>
                    <v-text-field type="tel" :error="user.mobile == ''" label="Mobilnummer" outlined v-model="user.mobile"></v-text-field>
                    <v-text-field label="IBAN" :error="user.iban == ''" outlined v-model="user.iban"></v-text-field>
                    <v-text-field label="PLZ" :error="user.zip == ''" outlined v-model="user.zip"></v-text-field>
                    <v-text-field label="Wohnort" :error="user.city == ''" outlined v-model="user.city"></v-text-field>
                  </v-expansion-panel-content>
                </v-expansion-panel>
              </v-expansion-panels>

              <v-btn class="primary full-width mt-5" @click="confirmSale" :loading="loading" :disabled="!formValid">Verkaufen</v-btn>
            </div>

          <transition name="fade">
            <div v-if="confirmed" class="text-main center">
              <v-img class="full-cover small elevation-2 mt-2" :aspect-ratio="item.cover.width/item.cover.height" :src="item.cover.data.thumbnails[5].url"></v-img>
              <h2 class="item-title">{{ item.title }}</h2>
              <v-icon class="mt-5" large>mdi-check-circle</v-icon>
              <h2 class="my-5">{{ $t('bookstore.your_copy_code') }}</h2>
              <p>Abschliessend musst du dein Buch mit folgendem Code kennzeichnen:</p>
              <div class="copy-uid-large mb-4">{{ copy.uid.substring(0,3) }} {{ copy.uid.substring(3,6) }}</div>
              <p>Schreibe ihn mit grossen Buchstaben auf einen Zettel und klebe diesen gut sichtbar auf das Buch. Dieses kannst du während den Öffnungszeiten beim Bookstore PickUp vorbeibringen.</p>

              <v-btn @click="goBack" class="primary full-width mt-2">Das Buch ist markiert</v-btn>
            </div>
          </transition>
          </v-card-text>
    </v-dialog>

    <div class="mb-4"></div>
  </div></div>
</template>

<script>
import api from "../../business/api.js"

export default {
  data(){
    return {
      loading: false,
      showDialog: false,
      item: null,
      isbnInput: null,
      isbn: null,
      confirmSaleVisible: false,
      showScanner: false,
      showManually: false,
      notFound: false,
      price: null,
      priceError: false,
      confirmed: false,
      loading: false,
      edition: null,

      copy: null,

      expansionPanels: null,
      verifyingAccount: false,
    }
  },

  computed: {
    user(){
      return this.$store.state.user
    },
    editions(){
      if(!this.item){
        return []
      }
      if(!this.item.editions){
        return []
      }
      var editions = []
      for(var edition of this.item.editions.sort((a, b) => (a.number < b.number) ? 1 : -1)){
        var t = `${edition.number}. Auflage (${edition.year})`
        if(edition.name){
          t += ' '+edition.name
        }
        editions.push({text: t, value: edition.id})
      }
      return editions
    },
    priceHint(){
      if(!this.item || this.item.copies.length == 0){
        return ''
      }
      if(this.item.copies.length == 1){
        return 'Es ist bereits ein Exemplar für CHF '+this.item.copies[0].price+'.- im Bookstore vorhanden'
      }
        var min = 100000000
        var max = 0
        for(var copy of this.item.copies){
          if(copy.price > max){
            max = copy.price
          }
          if(copy.price < min){
            min = copy.price
          }
        }
        return 'Es sind bereits Exemplare zwischen CHF '+min+'.- und '+max+'.- im Bookstore vorhanden'
    },
    emailValid(){
      var email = this.user.email
      if(!email){
        return false
      }
      email = email.toLowerCase()
      if(email.match(/.+@(edu\.)?sbl.ch/g)){
        return false;
      }
      if(!email.match(
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/g
      )){
        return false
      }
      return true
    },

    formValid(){
      var user = this.user
      return user.email && user.mobile && user.iban && user.zip && user.city && this.emailValid;
    },

    editionValid(){
      if(!this.item.editions || this.item.editions.length == 0){
        return true
      }

      if(!this.edition){
        return false
      }

      return true
    },

    payback(){
      var pb = Math.floor(this.price*(1-api.commission)*100)/100
      return (Math.round(pb * 100) / 100).toFixed(2);
    }
  },

  watch: {
    isbn(val){
      if(this.isbn.length > 4){
        this.checkIsbn(this.isbn)
      }
    },

    isbnInput(val){
      this.isbn = val.replaceAll(/[^0-9]/g, '')
    },

    price(val){
      this.validatePrice()
    }
  },

  methods: {
    async goToConfirmation(){
      this.verifyingAccount = true
      var user = await api.getUserInfo()
      if(user == null){
        this.verifyingAccount = false
        return false
      }
      this.user = user
      if(!this.formValid){
        this.expansionPanels = 0
      }
      this.verifyingAccount = false
      this.confirmSaleVisible = true
    },

    async confirmSale(){
      this.loading = true
      var updateUser = await api.updateUserInfo({
        email: this.user.email,
        mobile: this.user.mobile,
        iban: this.user.iban,
        zip: this.user.zip,
        city: this.user.city,
      })
      if(updateUser == null){
        this.loading = false
        return false
      }

      this.copy = await api.submitCopy({
        item: this.item.id,
        price: this.price,
        edition: this.edition
      })

      if(this.copy.id){
        this.confirmed = true
      }

      this.loading = false
    },

    validatePrice(){
      if(this.price != Math.round(this.price)){
        this.price = Math.round(this.price)
        this.priceError = true
      }
      else if(this.price >= 1 && this.price <= 200){
        this.priceError = false
      }
      else{
        this.priceError = true
      }
    },
    async checkIsbn(isbn){
      this.isbnInput = isbn
      var results = (await api.fetch("items/items?fields=*.*&filter[isbn]="+isbn)).data
      if(results[0]){
        this.item = results[0]
        this.notFound = false
        this.showScanner = false
      }
      else{
        this.showScanner = false
        this.showManually = true
        if(this.isbn.length >= 13 && !this.item){
          this.notFound = true
        }
        else{
          this.notFound = false
        }
      }
    },
    checkIsbnInput(e){
      if(e.key.match(/[^0-9]/) && e.key != "Backspace" && !e.metaKey){
        e.preventDefault()
        return false
      }
    },
    checkPriceInput(e){
      this.checkIsbnInput(e)
      if(e.target.value == '' && e.key == "0"){
        e.preventDefault()
        return false
      }
    },
    goBack(){
      window.bookstore.goBack();
    }
  },

  mounted(){

  }
}
</script>

<style lang="css" scoped>
</style>
