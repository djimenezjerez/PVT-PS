<template>

 <v-card class="elevation-12">
    <v-card-title>
      Prestamos Negativos
      <!-- <v-btn @click="ver">ver</v-btn>  -->
      <v-spacer></v-spacer>
      <v-text-field
        v-model="search"
        append-icon="search"
        label="Buscar"
        single-line
        hide-details
      ></v-text-field>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="loans"
      :search="search"
    >
      <template slot="items" slot-scope="props">
        <td>{{ props.item.PresNumero }}</td>
        <td class="text-xs-right">{{ props.item.AmrTotPag }}</td>
        <td class="text-xs-right">{{ props.item.AmrSldAnt }}</td>
        <td class="text-xs-right">{{ props.item.AmrOtrCob }}</td>
        <td class="text-xs-right">{{ props.item.AmrIntPen }}</td>
        <td class="text-xs-right">{{ props.item.AmrInt }}</td>
      </template>
      <v-alert slot="no-results" :value="true" color="error" icon="warning">
        Your search for "{{ search }}" found no results.
      </v-alert>
    </v-data-table>
  </v-card>
  
</template>
<script>
export default {
    data() {
            return {
            loans: [],
            search: '',
            headers: [
              {
                text: 'Numero de Prestamo',
                align: 'left',
                sortable: true,
                value: 'PresNumero'
              },
              { text: 'Total Pagado', value: 'AmrTotPag' },
              { text: 'Saldo Anterior', value: 'AmrSldAnt' },
              { text: 'Otros Cobros', value: 'AmrOtrCob' },
              { text: 'Interes Penal', value: 'AmrIntPen' },
              { text: 'Interes ', value: 'AmrInt' }
            ],
            }
    },
    created(){
           var self = this; 
          axios
           .get('/api/negative_loans')
           .then(function(response) {
                //this.data.loans = response.data;
                console.log('obteniendo lista ')
                self.loans = response.data;
              //  console.log(dat.loans);
            });
  
    },
    // define methods under the `methods` object
    methods: {
      ver(){
        console.log(this.loans)
      }
    
    }
}
</script>
