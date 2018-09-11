<template>
 <v-card class="elevation-12">
    <v-card-title>
      Prestamos
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
        <td class="text-xs-right">{{ props.item.PresFechaDesembolso }}</td>
        <td class="text-xs-right">{{ props.item.PresCuotaMensual }}</td>
        <td class="text-xs-right">{{ props.item.PresSaldoAct }}</td>
        <td class="text-xs-right">{{ props.item.PadTipo }}</td>
        <td class="text-xs-right">{{ props.item.PadCedulaIdentidad }}</td>
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
              { text: 'Fecha Desembolso', value: 'PresFechaDesembolso' },
              { text: 'Cuota', value: 'PresCuotaMensual' },
              { text: 'Saldo', value: 'PresSaldoAct' },
              { text: 'Tipo', value: 'PadTipo' },
              { text: 'CI ', value: 'PadCedulaIdentidad' }
            ],
            }
    },
    created(){
          var self = this; 
          axios
           .get('/api/loans_senasir')
           .then(function(response) {
                //this.data.loans = response.data;
                console.log('obteniendo lista ')
                self.loans = response.data;
                console.log(self.loans);
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
