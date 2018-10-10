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
