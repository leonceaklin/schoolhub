Vue.component('bookstore', {
  template: '<div class="bookstore">'+document.getElementById("bookstore").innerHTML+'</div>',
  data(){
    return {
      page:{
        name: "subjects",
        params: {}
      },
      history: [],
    }
  },
  methods: {
    goTo(page){
      this.history.push(this.page)
      this.page = page
      console.log(this.history)
    },
    goBack(){
      this.page = this.history.pop()
    }
  },
  mounted(){
    window.bookstore = this
  }
})

Vue.component('subjects-page', {
  template: '<div>'+document.getElementById("subjects-page").innerHTML+'</div>',
  data(){
    return {
      subjects: [],
      query: '',
    }
  },
  async mounted(){
    var response = await bookApi.fetch("items/subjects?fields=title,items.*,items.cover.*")
    this.subjects = response.data
    console.log('fetched', this.subjects)
    if(window.bookstore.page.params.query){
      this.query = window.bookstore.page.params.query
    }
  },
  watch: {
    query(val){
      window.bookstore.page.params.query = val
    },
  },
  computed: {
    relevantSubjects(){
      var accountSubjects = []
      var relSubjects = []
      for(var subject of window.app.subjects){
        accountSubjects.push(subject.name)
      }

      for(var subject of this.subjects){
        if(accountSubjects.includes(subject.title)){
          if(subject.items.length > 0){
            relSubjects.push(subject)
          }
        }
      }

      return relSubjects
    }
  },
  methods: {
    goToSellItemPage(){
      window.bookstore.goTo({
        name: "sell-item",
        params: {},
      })
    }
  }
})

Vue.component('item-preview', {
  template: '<div class="item-preview">'+document.getElementById("item-preview").innerHTML+'</div>',
  props: ["item"],
  methods: {
    viewItem(item){
      window.bookstore.goTo({
        name: "item",
        params: {item_id: item.id, item: item}
      })
    }
  }
})

Vue.component('items-display', {
  template: '<div class="items-display">'+document.getElementById("items-display").innerHTML+'</div>',
  props: ["items"],
})

Vue.component('item-page', {
  template: '<div class="item-page">'+document.getElementById("item-page").innerHTML+'</div>',

  data(){
    return {
      item: null,
      copiesVisible: false,
      confirmOrderVisible: false,
      selectedCopy: null,
    }
  },

  computed: {
    mainElement(){
      return this
    },

    bookstore(){
      return window.bookstore
    },

    mainButtonText(){
      if(this.selectedCopy == null){
        if(!this.item.copies[0].price){
          return `${this.item.copies.length} Exemplar${(this.item.copies.length > 1 ? 'e' : '')}`
        }
        return `${this.item.copies.length} Exemplar${(this.item.copies.length > 1 ? 'e ab' : ' für')} ${this.item.copies[0].price}.-`
      }
      else{
        return "Jetzt bestellen"
      }
    }
  },

  methods: {
    viewCopies(){
      if(!this.copiesVisible){
        this.copiesVisible = true
      }
      else{
        this.confirmOrderVisible = true
      }
    },

    selectCopy(copy){
      this.selectedCopy = copy
    }
  },

  async mounted(){
    this.item =  window.bookstore.page.params.item
    this.item.copies = (await bookApi.fetch("items/copies?fields=id,condition,edition.*,price,status&filter[status]=available&filter[ordered_by][null]=&sort=price&filter[item]="+window.bookstore.page.params.item_id)).data

    if(this.item.copies.length == 1){
      this.copiesVisible = true
    }
  }
})

Vue.component('search-results', {
  template: '<div class="search-results">'+document.getElementById("search-results").innerHTML+'</div>',

  props: ['query'],
  data(){
    return {
      loading: false,
    }
  },

  computed: {

  },

  watch: {
    query(value){
      if(value != ''){
        this.fetchResults()
      }
    },
  },

  methods: {
    async fetchResults(){
      this.loading = true
      this.results = (await bookApi.fetch("items/items?fields=*.*&sort=-year&q="+this.query)).data

      console.log("Results:", this.results)
      this.loading = false
    },
  },

  async mounted(){
    if(this.query != ''){
      this.fetchResults()
    }
  }
})

Vue.component('copy-selector', {
  template: '<div class="copy-selector">'+document.getElementById("copy-selector").innerHTML+'</div>',

  props: ['copies'],
  data(){
    return {
      selectedCopy: null,
      conditions: ["Neuwertig", "Kleine Gebrauchsspuren", "Entfernbare Notizen", "Permanente Notizen", "Starke Gebrauchsspuren"]
    }
  },

  computed: {

  },

  watch: {
    selectedCopy(val){
      this.$emit('select', val)
    }
  },

  methods: {
    getCondition(copy){
      if(!copy.condition){
        return "Zustand unbekannt"
      }
      else{
        return this.conditions[copy.condition-1]
      }
    }
  },

  async mounted(){
    this.selectedCopy = this.copies[0]
  }
})

Vue.component('confirm-order', {
  template: '<div class="confirm-order">'+document.getElementById("confirm-order").innerHTML+'</div>',

  props: ['item', 'copy', 'mainElement'],
  data(){
    return {
      loading: false,
      orderConfirmed: false,
    }
  },

  computed: {
    app(){
      return window.app
    }
  },

  watch: {

  },

  methods: {
    async confirmOrder(){
      this.loading = true
      var response = await bookApi.orderCopyById(this.copy.id)
      this.loading = false
      if(response.data.copy.id == this.copy.id){
        this.orderConfirmed = true
      }
    },

    goBack(){
      this.mainElement.confirmOrderVisible = false
      window.bookstore.goBack();
    }
  },

  async mounted(){

  }
})

Vue.component('cancel-order', {
  template: '<div class="cancel-order">'+document.getElementById("cancel-order").innerHTML+'</div>',

  data(){
    return {
      loading: false,
      cancelConfirmed: false,
      showDialog: false
    }
  },

  computed: {
    app(){
      return window.app
    }
  },

  watch: {
    showDialog(val){
      if(val == false){
        history.replaceState && history.replaceState(null, '', location.pathname + location.search.replace(/[\?&]cancelorder=[^&]+/, '').replace(/^&/, '?'));
      }
    }
  },

  methods: {
    async cancelOrder(){
      this.loading = true
      var response = await bookApi.cancelOrderByHash(this.findGetParameter('cancelorder'))
      this.loading = false
      if(response.data.copy.id){
        this.cancelConfirmed = true
      }
    },

    findGetParameter(parameterName) {
      var result = null,
          tmp = [];
      location.search
          .substr(1)
          .split("&")
          .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
          });
      return result;
    }
  },

  async mounted(){
    if(this.findGetParameter('cancelorder')){
      this.showDialog = true
    }
  }
})

Vue.component('sell-item-page', {
  template: '<div class="sell-item-page">'+document.getElementById("sell-item-page").innerHTML+'</div>',

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

      user: {
        email: null,
        mobile: null,
        iban: null,
        zip: null,
        city: null,
      },

      copy: null,

      expansionPanels: null,
      verifyingAccount: false,
    }
  },

  computed: {
    app(){
      return window.app
    },
    bookstore(){
      return window.bookstore
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
      var pb = Math.floor(this.price*(1-bookApi.commission)*100)/100
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
      var user = await bookApi.getUserInfo()
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
      var updateUser = await bookApi.updateUserInfo({
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

      this.copy = await bookApi.submitCopy({
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
      var results = (await bookApi.fetch("items/items?fields=*.*&filter[isbn]="+isbn)).data
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
})

Vue.component('barcode-scanner', {
  template: '<div class="barcode-scanner">'+document.getElementById("barcode-scanner").innerHTML+'</div>',
  data(){
    return {
      scanner: null,
      code: null,
    }
  },

  beforeDestroy(){
    Quagga.stop()
  },

  methods: {
    onDetected(data){
      this.$emit('scan', this.code)
    },
    onProcessed(data){
      if(data && data.codeResult && data.codeResult.code){
        if(data.codeResult.code != this.code && data.codeResult.code != null){
          this.code = data.codeResult.code
          this.onDetected(data)
        }
      }
    }
  },

  mounted(){
    this.code = null
    Quagga.init({
      inputStream : {
        name : "Live",
        type : "LiveStream",
        target: this.$refs.scannerContainer
      },
      locate: false,
      decoder : {
        readers : ["ean_reader"],
        multiple: false
      },
    },
    (err) => {
      if(err){
        this.$emit('error', err)
        return
      }
      Quagga.start();
      Quagga.onProcessed((d) => { try{this.onProcessed(d)}catch(e){} })
    });
  }
})
