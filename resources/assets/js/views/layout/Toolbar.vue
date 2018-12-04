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
      <!-- <v-text-field
        flat
        solo-inverted
        prepend-icon="search"
        label="Buscar"
        class="hidden-sm-and-down"
      ></v-text-field> -->
      <v-spacer></v-spacer>
     
      <v-menu
        v-model="menu"
        :close-on-content-click="false"
        :nudge-width="100"
        offset-x
        v-if="user"
      >
        <v-btn 
          slot="activator"
          icon
          large
          
        >
        
        <v-icon>person</v-icon>
        
      </v-btn>

      <v-card   light>
        <v-list>
          <v-list-tile avatar>
            <v-list-tile-avatar>
              <img src="https://cdn.vuetifyjs.com/images/john.jpg" alt="John">
            </v-list-tile-avatar>

            <v-list-tile-content >
              <v-list-tile-title>{{user.first_name+' '+user.last_name}}</v-list-tile-title>
              <v-list-tile-sub-title>{{user.position}}</v-list-tile-sub-title>
              
            </v-list-tile-content>

          </v-list-tile>
        </v-list>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-tooltip bottom>
            <v-btn
              slot="activator"
              color="primary"
              dark
              flat @click="menu = false"
            >
             <v-icon>reply</v-icon> 
            </v-btn>
            <span>Regresar</span>
          </v-tooltip>
           <v-tooltip bottom>
            <v-btn
              slot="activator"
              color="primary"
              dark
              flat @click="logout"
            >
             <v-icon>exit_to_app</v-icon> 
            </v-btn>
            <span>Cerrar Sesion</span>
          </v-tooltip>
        </v-card-actions>
      </v-card>
    </v-menu>
    </v-toolbar>
</template>
<script>
import { mapState } from 'vuex';
export default {
    data: () => ({
      menu: false, 
    }),
    computed:{
       drawer:{
          get(){
            return this.$store.state.template.drawer;
          },
          set(value){
            this.$store.commit('template/updateDrawer',value);
          }
        },
        user(){
          return this.$store.state.auth.user;
        }
    },
    methods:{
      logout()
      {
        this.$store.dispatch('auth/logout')
          .then(() => this.$router.push('/login'))
          .catch(err => console.log(err))
      }
    }
}
</script>
