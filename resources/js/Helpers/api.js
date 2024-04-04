import axios from 'axios';

const apiClient = axios.create({
  //baseURL: 'http://premier.census.net/api/',
  //baseURL: 'http://rmci-testserver/api/',
  baseURL: 'http://DESKTOP-HKDISKR/api/',
  //baseURL: 'http://premier_nephro.care.net/api/',ss
  headers: {
    'Content-Type': 'application/json',
    'Authorization': "Bearer ".concat(localStorage.getItem('token')),
  },
});

export default {
  get(url) {
    return apiClient.get(url);
  },
  post(url,data) {
    return apiClient.post(url, data)/* .catch(error => {
      if(error.response.data.message == 'Token has expired'){
       this.$router.push({ name: '/' });
       Toast.fire({
         icon: 'error',
         title: 'Token has expired'
       })
      }
   }); */
  },
  update(id, data) {
    return apiClient.put(`/items/${id}`, data);
  },
  delete(url) {
    return apiClient.delete(url);
  },
};
