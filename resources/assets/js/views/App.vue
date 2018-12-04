<template>
 <v-app id="inspire">
    <router-view name="nomenu"></router-view>
    <router-view name="toolbar"></router-view>
    <router-view name="nav"></router-view>
    <v-content>
    <v-container fluid fill-height>
      <v-layout column fill-height>
         <router-view></router-view>
        </v-layout>
        </v-container>
    </v-content>
   
 </v-app>
</template>
<script>
export default {
        data: () => ({
      dialog: false,
    }),
    computed:{
      // currentUser(){
      //   return this.$store.getters.currentUser;
      // }
      getToken(){
        return this.$store.state.auth.token;
      }
    },
    created(){
      //console.log(this.getToken);
      const user = localStorage.getItem('token');
      // console.log(user['first_name']);
      console.log("setteando token");
      axios.defaults.headers.common['Authorization'] = 'Bearer '+this.getToken;
    }
}
</script>

