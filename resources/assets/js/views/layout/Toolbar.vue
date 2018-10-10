<template>
    <v-toolbar
      color="blue darken-2"
      dark
      app
      :clipped-left="$vuetify.breakpoint.mdAndUp"
      fixed
    >
      <v-toolbar-title style="width: 300px" class="ml-0 pl-3">
        <v-toolbar-side-icon @click.stop="drawer = !drawer"></v-toolbar-side-icon>
        <span class="hidden-sm-and-down">Prestamos</span>
      </v-toolbar-title>
      <v-text-field
        flat
        solo-inverted
        prepend-icon="search"
        label="Buscar"
        class="hidden-sm-and-down"
      ></v-text-field>
      <v-spacer></v-spacer>
     
      <v-btn  icon>
        <v-icon>notifications</v-icon>
      </v-btn>
      <v-btn   icon large >
         
           <v-avatar size="32px" tile>
              <v-icon > 
                  person
              </v-icon>
            </v-avatar>
        
       
      </v-btn>
    </v-toolbar>
</template>
<script>
export default {
    computed:{
        drawer:{
          get(){
            return this.$store.state.drawer;
          },
          set(value){
            this.$store.commit('updateDrawer',value);
          }
        }
    }
}
</script>
