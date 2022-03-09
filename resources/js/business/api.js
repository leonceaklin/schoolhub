import store from "../plugins/store"

class SchoolHubApi{
  constructor(data){
    this.catalogUrl = data.catalogUrl
    this.apiUrl = data.apiUrl
    this.commission = 0.15
  }

  set authToken(val){
    return store.dispatch('setAuth', val)
  }

  get authToken(){
    return store.state.auth
  }

  set school(val){
    return store.dispatch('setSchool', val)
  }

  get school(){
    return store.state.school
  }

  set credentialsToken(val){
    return store.dispatch('setCredentialsToken', val)
  }

  get credentialsToken(){
    return store.state.credentialsToken
  }

  get schoolSystemApiUrl(){
    return this.apiUrl+"/sal/"+this.school
  }

  async fetch(endpoint, data){
    var response = await axios.get(this.catalogUrl+"/"+endpoint)
    return response.data
  }

  async fetchSchools(){
    var response = await axios.get(`${this.apiUrl}/schools`)
    if(response.data){
      return response.data
    }
  }

  async fetchSchoolSystemSubjects(){
    var response = await axios.get(`${this.schoolSystemApiUrl}/subjects`,{headers: {"Authorization": "Bearer "+this.credentialsToken}})
    if(response.data){
      return response.data
    }
  }

  async fetchSchoolSystemAbsenceInformation(){
    var response = await axios.get(`${this.schoolSystemApiUrl}/absence_information`,{headers: {"Authorization": "Bearer "+this.credentialsToken}})
    if(response.data){
      return response.data
    }
  }

  async fetchSchoolSystemEvents(){
    var response = await axios.get(`${this.schoolSystemApiUrl}/events`,{headers: {"Authorization": "Bearer "+this.credentialsToken}})
    if(response.data){
      return response.data
    }
  }

  async fetchSchoolSystemUser(){
    var response = await axios.get(`${this.schoolSystemApiUrl}/user`,{headers: {"Authorization": "Bearer "+this.credentialsToken}})
    if(response.data){
      return response.data
    }
  }

  async fetchSchoolSystemClass(){
    var response = await axios.get(`${this.schoolSystemApiUrl}/class`,{headers: {"Authorization": "Bearer "+this.credentialsToken}})
    if(response.data){
      return response.data
    }
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
        credentials_token: window.app.credentialsToken,
      })
    }
    else{
      await this.register({
        credentials_token: window.app.credentialsToken,
        first_name: window.app.user.first_name,
        last_name: window.app.user.last_name,
        email: window.app.user.private_email,
        phone: window.app.user.phone,
        mobile: window.app.user.mobile,
        zip: window.app.user.zip,
        city: window.app.user.city,
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
    var response = await this.postToEndpoint('bookstore:copies', data)
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

  async schoolSystemLogin(credentials){
    var auth = btoa(credentials.username+":"+credentials.password);
    this.school = credentials.school
    var response = await axios.get(`${this.apiUrl}/sal/${this.school}/login`,{headers: {"Authorization": "Basic "+auth}})
    if(response.data){
      this.credentialsToken = response.data.data.token
      return response.data
    }
  }

  async checkAccountExistence(username){
    var response = await this.postToEndpoint('users:check', {
      username: username
    })
    return response.data.data.exists
  }

  async postToEndpoint(endpoint, data){
    var response = await axios.post(this.apiUrl+"/"+endpoint, data, this.requestOptions)
    return response
  }

  async getFromEndpoint(endpoint, data){
    var response = await axios.get(this.apiUrl+"/"+endpoint, this.requestOptions)
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

export default new SchoolHubApi({
  catalogUrl: "https://content.zebrapig.com/schoolhub",
  apiUrl: "http:/127.0.0.1:8000/api"
})
