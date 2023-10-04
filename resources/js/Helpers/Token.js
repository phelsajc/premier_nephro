class Token{

    isValid(token){
     const payload = this.payload(token)
     console.log("payload: ")
     console.log(payload)
     if (payload) {
      console.log("to login: "+payload.iss)
      return payload.iss = "http://premier.census.net/api/auth/login" || "http://premier.census.net/api/auth/register" ? true : false
      //return payload.iss = "http://rmci-testserver/api/auth/login" || "http://rmci-testserver/api/auth/register" ? true : false
     }
     return false
    }   
   
    payload(token){
     const payload = token.split('.')[1]
     return this.decode(payload)
    }

    decode(payload){
     return JSON.parse(atob(payload))
    } 
   
   }
   
   export default Token = new Token()