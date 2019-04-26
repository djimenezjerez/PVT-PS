<template>
 <v-card class="elevation-12">
    <v-card-title>
      Prestamos en Mora Pasivos 
      <v-btn small color="success" @click="download" 
    :disabled="dialog"
    :loading="dialog"
        ><v-icon>file_download</v-icon>Excel
        </v-btn>
      
        <v-dialog
          v-model="dialog"
          hide-overlay
          persistent
          width="300"
        >
          <v-card
            color="primary"
            dark
          >
            <v-card-text>
              Por favor espere
              <v-progress-linear
                indeterminate
                color="white"
                class="mb-0"
              ></v-progress-linear>
            </v-card-text>
          </v-card>
        </v-dialog>

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
        <td class="text-xs-left">{{ props.item.PresNumero }}</td>
        <td class="text-xs-left">{{ props.item.PresFechaDesembolso }}</td>
        <td class="text-xs-left">{{ props.item.PresCuotaMensual }}</td>
        <td class="text-xs-left">{{ props.item.PresSaldoAct }}</td>
        <td class="text-xs-left">{{ props.item.PadMatricula }}</td>
        <td class="text-xs-left">{{ props.item.PadCedulaIdentidad }}</td>
        <td class="text-xs-left">{{ props.item.PadNombres }}</td>
        <td class="text-xs-left">{{ props.item.PadNombres2do }}</td>
        <td class="text-xs-left">{{ props.item.PadPaterno }}</td>
        <td class="text-xs-left">{{ props.item.PadMaterno }}</td>
        <!-- <td class="text-xs-left">{{ props.item.State }}</td> -->
        <!-- <td class="text-xs-left">{{ props.item.City }}</td> -->
        <td class="text-xs-left">{{ props.item.Diff }}</td>
        <td class="text-xs-left">{{ Number(props.item.prestasaint).toFixed(2) }}</td>
        <td class="text-xs-left"> <a  v-bind:href="generate_link(props.item.IdPrestamo)"><v-icon>assignment</v-icon></a> </td>
      </template>
      <v-alert slot="no-results" :value="true" color="error" icon="warning">
        Su busqueda para "{{ search }}" no se encontraron resultados.
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
              { text: 'Numero de Prestamo', value: 'PresNumero' },
              { text: 'Fecha Desembolso', value: 'PresFechaDesembolso' },
              { text: 'Cuota', value: 'PresCuotaMensual' },
              { text: 'Saldo', value: 'PresSaldoAct' },
              { text: 'Matricula', value: 'PadMatricula' },
              { text: 'CI ', value: 'PadCedulaIdentidad' },
              { text: '1er Nombre', value: 'PadNombres' },
              { text: '2do Nombre ', value: 'PadNombres2do' },
              { text: 'Paterno ', value: 'PadPaterno' },
              { text: 'Materno ', value: 'PadMaterno' },
            //   { text: 'Frecuencia ', value: 'State' },
            //   { text: 'Departamento', value: 'City' },
            //   { text: 'Descuento ', value: 'Discount' },
              { text: 'Mese Mora ', value: 'Diff' },
              { text: 'Interes ', value: 'prestasaint' },
              { text: 'Accion ' }
            ],
            dialog: false,
            }
    },
    created(){
          var self = this; 
          axios
           .get('/api/loans_in_arrears')
           .then(function(response) {
                //this.data.loans = response.data;
                console.log('obteniendo lista ')
                self.loans = response.data;
                console.log(self.loans);
            });
  
    },
    
    // define methods under the `methods` object
    methods: {
      generate_link(id){
          return 'http://sismu.muserpol.gob.bo/musepol/akardex.aspx?'+id;
        //console.log(this.loans)
      },
       download: function (event) {
        // `this` inside methods point to the Vue instance
        self = this;
        self.dialog = true
        //  self.dialog = true;
        axios({
            url: '/api/loans_pasivo_mora_report',
            method: 'GET',
            responseType: 'blob', // important
          }).then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'prestamos en morar.xls');
            document.body.appendChild(link);
            link.click();
              self.dialog = false;
          });
      }
    
    },
     watch: {
      dialog (val) {
        if (!val) return

        //setTimeout(() => (this.dialog = false), 4000)
      }
    }
}
</script>
