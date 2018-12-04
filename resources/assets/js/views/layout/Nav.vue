<template>
    <v-navigation-drawer
      fixed
      :clipped="$vuetify.breakpoint.mdAndUp"
      app
      v-model="drawer"
    >
      <v-list dense>
        <template v-for="item in items">
          <v-layout
            row
            v-if="item.heading"
            align-center
            :key="item.heading"
          >
            <v-flex xs6>
              <v-subheader v-if="item.heading">
                {{ item.heading }}
              </v-subheader>
            </v-flex>
            <v-flex xs6 class="text-xs-center">
              <a href="#!" class="body-2 black--text">EDIT</a>
            </v-flex>
          </v-layout>
          <v-list-group
            v-else-if="item.children"
            v-model="item.model"
            :key="item.text"
            :to="item.link"
            :prepend-icon="item.model ? item.icon : item['icon-alt']"
            append-icon=""
          >
            <v-list-tile slot="activator">
              <v-list-tile-content>
                <v-list-tile-title>
                  {{ item.text }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-tile>
            <v-list-tile
              v-for="(child, i) in item.children"
              :key="i"
           
            >
              <v-list-tile-action v-if="child.icon">
                <v-icon>{{ child.icon }}</v-icon>
              </v-list-tile-action>
              <v-list-tile-content>
                <v-list-tile-title>
                  {{ child.text }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-tile>
          </v-list-group>
          <v-list-tile v-else  :key="item.text"  :to="item.link">
            <v-list-tile-action>
              <v-icon>{{ item.icon }}</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
              <v-list-tile-title>
                {{ item.text }}
              </v-list-tile-title>
            </v-list-tile-content>
          </v-list-tile>
        </template>
      </v-list>
    </v-navigation-drawer>
</template>
<script>
export default {
    data(){
        return {
            items: [
                { icon: 'dashboard', text: 'Inicio' , link: '/' },
                { icon: 'work', text: 'Prestamos Vigentes' , link: '/loans' },
                { icon: 'date_range', text: 'Prestamos en Mora' , link: '/overdue_loans' },
                { icon: 'query_builder', text: 'Mora Total' , link: '/total_overdue_loans' },
                { icon: 'card_travel', text: 'Mora Parcial' , link: '/partial_loans' },
                { icon: 'insert_drive_file', text: 'Nuevos  Comando' , link: '/news_comand' },
                { icon: 'folder_shared', text: 'Prestamos  Comando ' , link: '/loans_command' },
                { icon: 'insert_drive_file', text: 'Nuevos  Senasir' , link: '/news_senasir' },
                { icon: 'folder_shared', text: 'Recurrentes  Senasir' , link: '/loans_senasir' },
                // { icon: 'assignment_late', text: 'Prestamos Negativos' , link: '/negative_loans' },
                { icon: 'assignment_late', text: 'Prestamos en Mora Pasivos' , link: '/loans_in_arriears' },
                // { icon: 'group', text: 'Reportes' , link: '/report' },
                { icon: 'fa-money', text: 'Amortizaciones ' , link: '/amortization' },
            ]
        };
    },
    computed:{
        drawer:{
          get(){
            return this.$store.state.template.drawer;
          },
          set(value){
            this.$store.commit('template/updateDrawer',value);
          }
        }
    }

}
</script>
