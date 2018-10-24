
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vuetify from 'vuetify';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import App from './views/App';
import {routes} from './routes.js';
import {storage} from './storage.js';


window.Vue = require('vue');
window.moment = require('moment');
window.Chart = require('chart.js');


require('moment/locale/en-ca');

Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(Vuex);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const router = new VueRouter({
  mode: 'history',
  routes
});

const store = new Vuex.Store(storage);

const app = new Vue({
  el: '#app',
  components: { App },
  router,
  store
});