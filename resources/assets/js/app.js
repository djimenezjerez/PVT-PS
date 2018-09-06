
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vuetify from 'vuetify';
import VueRouter from 'vue-router';
window.Vue = require('vue');
Vue.use(Vuetify)
Vue.use(VueRouter)
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.component('example-component', require('./components/ExampleComponent.vue'));
import App from './views/App';
import Home from './views/Welcome';
import Report from './views/Report';
import NegativeLoans from './views/NegativeLoans';

const router = new VueRouter({
  mode: 'history',
  routes: [
      {
          path: '/',
          name: 'home',
          component: Home
      },
      {
          path: '/report',
          name: 'report',
          component: Report
      },
      {
          path: '/negative_loans',
          name: 'negavitve_loans',
          component: NegativeLoans
      },
      
  ],
});



const app = new Vue({
  el: '#app',
  components: { App },
  router,
});