import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://premier.census.net/api/',
  //baseURL: 'http://premier_nephro.care.net/api/',
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
    return apiClient.post(url, data);
  },
  update(id, data) {
    return apiClient.put(`/items/${id}`, data);
  },
  delete(url) {
    return apiClient.delete(url);
  },
};
