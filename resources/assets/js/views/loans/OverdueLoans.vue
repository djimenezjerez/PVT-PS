<template>
 <v-card class="elevation-12">
    <v-card-title>
      Prestamos en Mora

            <v-btn small @click="download" 
                :disabled="dialog"
                :loading="dialog"
                icon
                > <v-icon color="success"> fa-file-excel-o</v-icon>
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
        <v-menu
          :close-on-content-click="false"
          v-model="menu2"
          :nudge-right="40"
          lazy
          transition="scale-transition"
          offset-y
          full-width
          max-width="290px"
          min-width="290px"
        >
          <v-text-field
            slot="activator"
            v-model="date"
            label="Fecha"
            hint="Año-Mes-Dia"
            persistent-hint
            prepend-icon="event"
            readonly
          ></v-text-field>
          <v-date-picker v-model="date" no-title @input="menu2 = false"></v-date-picker>
        </v-menu>
        <v-btn icon>
            <v-icon color="success" @click="buscar()">refresh</v-icon>
        </v-btn>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="loans"
      :search="search"
    >
      <template slot="items" slot-scope="props">
        <td class="text-xs-left">{{ props.item.PresNumero }}</td>
        <td class="text-xs-left">{{ props.item.PresSaldoAct }}</td>
        <td class="text-xs-left">{{ props.item.PadMatricula }}</td>
        <td class="text-xs-left">{{ props.item.PadCedulaIdentidad }}</td>
        <td class="text-xs-left">{{ props.item.PadNombres }}</td>
        <td class="text-xs-left">{{ props.item.PadNombres2do }}</td>
        <td class="text-xs-left">{{ props.item.PadPaterno }}</td>
        <td class="text-xs-left">{{ props.item.PadMaterno }}</td>
        <td class="text-xs-left">{{ props.item.PadTipo }}</td>
        <td class="text-xs-left">{{ props.item.meses_mora }}</td>
        
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
              { text: 'Saldo', value: 'PresSaldoAct' },
              { text: 'Matricula', value: 'PadMatricula' },
              { text: 'CI ', value: 'PadCedulaIdentidad' },
              { text: '1er Nombre', value: 'PadNombres' },
              { text: '2do Nombre ', value: 'PadNombres2do' },
              { text: 'Paterno ', value: 'PadPaterno' },
              { text: 'Materno ', value: 'PadMaterno' },
              { text: 'Tipo ', value: 'PadTipo' },
              { text: 'Mora ', value: 'meses_mora' },
              { text: 'Accion ' }
            ],
            dialog: false,
            date: '2018-08-31',
            dateFormatted: null,
            menu2: false
            }
    },
    created(){
            axios.get('/api/overdue_loans')
            .then((response)=>{
                    console.log('obteniendo lista ')
                    this.loans = response.data;
                });

            console.log(moment().format());
  
    },
    
    // define methods under the `methods` object
    methods: {
        generate_link(id){
            return 'http://sismu.muserpol.gob.bo/musepol/akardex.aspx?'+id;
            //console.log(this.loans)
        },
         buscar(){
            console.log("buscando al hdp");
            console.log(this.getParams());

             axios({
                url: '/api/overdue_loans',
                method: 'GET',
                params: this.getParams(),   
            }).then((response) => {
               this.loans = response.data;
            });
            // axios.get('/api/overdue_loans',this.getParams())
            //      .then((response)=>{
            //             console.log('obteniendo lista ')
            //             this.loans = response.data;
            //         });
        },
        getParams(){
            let params={};
            params.date = this.date;
            return params;
        },
        download: function (event) {
            // `this` inside methods point to the Vue instance
            // self = this;
            // self.dialog = true
            let parameters = this.getParams();
             parameters['excel']=true;
            this.dialog = true;
            axios({
                url: '/api/overdue_loans',
                method: 'GET',
                params: parameters,
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Mora '+moment().format()+'.xls');
                document.body.appendChild(link);
                link.click();
                this.dialog = false;
            });
        },
        formatDate (date) {
            if (!date) return null

            const [year, month, day] = date.split('-')
            return `${month}/${day}/${year}`
        },
        parseDate (date) {
            if (!date) return null

            const [month, day, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
        }
    
    },
    watch: {
      dialog (val) {
        if (!val) return

        //setTimeout(() => (this.dialog = false), 4000)
      },
      date (val) {
          console.log(this.date);
        this.dateFormatted = this.formatDate(this.date)
      }
    },
    computed: {
      computedDateFormatted () {
        return this.formatDate(this.date)
      }
    },

}
</script>

