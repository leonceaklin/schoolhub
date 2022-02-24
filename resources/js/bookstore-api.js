class BookstoreApi{
  constructor(data){
    this.catalogUrl = data.catalogUrl
    this.apiUrl = data.apiUrl
    this.commission = 0.15
  }


  set authToken(val){
    return window.localStorage.setItem("auth", val)
  }

  get authToken(){
    return window.localStorage.getItem("auth")
  }

  async fetch(endpoint, data){
    var response = await axios.get(this.catalogUrl+"/"+endpoint)
    return response.data
  }

  async orderCopyById(id){
    await this.loginOrRegister()
    var response = await this.postToEndpoint('copies:order', {
      id: id
    })
    return response.data
  }

  async cancelOrderByHash(hash){
    var response = await this.postToEndpoint('copies:cancelorder', {
      order_hash: hash
    })
    return response.data
  }

  async loginOrRegister(){
    if(await this.isLoggedIn()){
      return false
    }
    var accountExists = await this.checkAccountExistence(window.app.user.username)
    if(accountExists){
      await this.login({
        username: window.app.username,
        password: window.app.password
      })
    }
    else{
      await this.register({
        username: window.app.username,
        password: window.app.password,
        first_name: window.app.user.first_name,
        last_name: window.app.user.last_name,
        email: window.app.user.private_email,
        phone: window.app.user.phone,
        mobile: window.app.user.mobile
      })
    }
  }

  async isLoggedIn(){
    var response = await this.getFromEndpoint('users:me')
    if(response.data && response.data.data && response.data.data.username){
      return true
    }
    return false
  }

  async getUserInfo(){
    await this.loginOrRegister()
    var response = await this.getFromEndpoint('users:me')
    if(response.data && response.data.data && response.data.data.id){
      return response.data.data
    }
    return null
  }

  async updateUserInfo(data){
    this.loginOrRegister()
    var response = await this.postToEndpoint('users:update', data)
    if(response){
      return response.data
    }
    return null
  }

  async submitCopy(data){
    this.loginOrRegister()
    var response = await this.postToEndpoint('copies:submit', data)
    return response.data.data.copy
  }


  async login(userData){
    var response = await this.postToEndpoint('users:login', userData)
    if(response.data.data.token){
      this.authToken = response.data.data.token
    }
  }

  async register(userData){
    var response = await this.postToEndpoint('users:register', userData)
    if(response.data.data.token){
      this.authToken = response.data.data.token
    }
  }

  async checkAccountExistence(username){
    var response = await this.postToEndpoint('users:check', {
      username: username
    })
    return response.data.data.exists
  }

  async postToEndpoint(endpoint, data){
    var response = await axios.post(this.apiUrl+"/?endpoint="+endpoint, data, this.requestOptions)
    return response
  }

  async getFromEndpoint(endpoint, data){
    var response = await axios.get(this.apiUrl+"/?endpoint="+endpoint, this.requestOptions)
    return response
  }

  get requestOptions(){
    var options = {}
    if(this.authToken){
      options.headers = {
        "Authorization" : `Bearer ${this.authToken}`
      }
    }
    return options
  }
}

var bookApi = new BookstoreApi({
  catalogUrl: "https://content.zebrapig.com/schoolhub",
  apiUrl: "/api/bookstore"
})
