require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)

//Router Imported
import {routes} from './routes'

// Import User Class
import User from './Helpers/User'
window.User = User

// Import Notification Class
import Notification from './Helpers/Notification'
window.Notification = Notification

//Sweetalert2
import Swal from 'sweetalert2';
window.Swal = Swal;



/* Vue.component('Autocomplete',require('./components/Autocomplete.vue')); */
/* require('./components/Autocomplete.vue').default */


import VuePdfApp from "vue-pdf-app";
window.VuePdfApp = VuePdfApp;
Vue.component('phicModal', require('./components/modals/phicPayment.vue').default);
Vue.component('addSessionModal', require('./components/modals/addSession.vue').default);
Vue.component('patientComponent', require('./components/Autocomplete.vue').default);
Vue.component('companyComponent', require('./components/Company.vue').default);

Vue.component('diagnostic', require('./components/AutocompleteD.vue').default);
Vue.component('user-info', require('./components/User.vue').default);
Vue.component('pxmodal', require('./components/Prescription.vue').default);
Vue.component('navComponent', require('./components/template/nav.vue').default);
Vue.component('sidemenuComponent', require('./components/template/sidemenu.vue').default);
Vue.component('footerComponent', require('./components/template/footer.vue').default);
//Vue.component('loaderComponent', require('./components/template/loader.vue').default);
Vue.component('products', require('./components/Products.vue').default);
Vue.component('progressBar', require('./components/ProgressBar.vue').default);
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

window.Toast = Toast
  

const router = new VueRouter({
    routes,
    mode:'history'
})
  
import api from './Helpers/api';
window.api = api

const app = new Vue({
    el: '#app',
    router,
    data() {
        return {
            //bladeValue: '',
            //showModal: false,
            //isLogin: 'xxxx',
        }
    }
});
