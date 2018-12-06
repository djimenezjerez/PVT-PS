
window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}


import Vuetify from 'vuetify';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import App from './views/App';
import {routes} from './routes.js';
// import {storage} from './storage.js';
// import * as jsPDF  from 'jspdf';
import {storage} from './store_modules/storage';
import {autentication} from './store_modules/autentication';

// import XLSX from 'xlsx';
// window.jsPDF = require('jspdf');
window.Vue = require('vue');
window.moment = require('moment');
window.Chart = require('chart.js');
window.numeral = require('numeral');

require('moment/locale/en-ca');

Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(Vuex);

Vue.prototype.$http = axios;
const tokenJWT = localStorage.getItem('token')
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
if (tokenJWT) {
  Vue.prototype.$http.defaults.headers.common['Authorization'] = tokenJWT
}
const store = new Vuex.Store({
    modules:{
        template: storage,
        auth: autentication,
    }
});


const router = new VueRouter({
  mode: 'history',
  routes
});

// const store = new Vuex.Store(storage);
router.beforeEach((to, from, next) => {
  if(to.matched.some(record => record.meta.requiresAuth)) {
    if (store.getters['auth/isLoggedIn']) {
      next()
      return
    }
    next('/login') 
  } else {
    next() 
  }
});
const app = new Vue({
  el: '#app',
  components: { App },
  router,
  store
});