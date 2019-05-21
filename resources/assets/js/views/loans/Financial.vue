<template>
  <v-card>
    <v-card-title>
      Financiera
      <v-btn icon  @click="download">
        <v-icon color="gray">
          fa-file-text
        </v-icon>
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
      <v-flex xs1 sm1 md1>
        <v-combobox
        v-model="paginationRows"
        :items="pagination_select"
        label="Mostrar Registros"
        @change="search()"
        ></v-combobox>
      </v-flex>
    </v-card-title>
    <v-data-table
      :headers="headers"
      :items="loans"
      hide-actions
    >
      <template slot="headers" slot-scope="props" >
        <tr>
          <th v-for="(header,index) in props.headers" :key="index" class="text-xs-left">
            <v-flex v-if="header.value!='actions'">
              <v-tooltip bottom>
                <span slot="activator">{{header.text}}</span>
                <span >{{header.input}}</span>
              </v-tooltip>
              <v-menu  v-model="header.menu"
                :close-on-content-click="false"
              >
                <v-btn
                  slot="activator"
                  icon
                  v-if="header.sortable!=false"
                >
                  <v-icon  small :color="header.input!=''?'blue':'black'">fa-filter</v-icon>
                </v-btn>
                <v-card  v-if="header.type=='text'">
                  <v-text-field
                    outline
                    hide-details
                    v-model="header.input"
                    append-icon="search"
                    :label="`Buscar ${header.text}...`"
                    @keydown.enter="search()"
                    @keyup.delete="checkInput(header.input)"
                    @keyup.esc="header.menu=false"
                  ></v-text-field>
                </v-card>
                <v-card  v-if="header.type=='date'">
                  <v-list>
                    <v-list-tile avatar>
                      <v-list-tile-content>
                        <v-menu
                          :close-on-content-click="false"
                          v-model="menu_date"
                          :nudge-right="40"
                          lazy
                          transition="scale-transition"
                          offset-y
                          full-width
                          max-width="290px"
                          min-width="290px"
                        >
                          <v-text-field
                            hide-details
                            slot="activator"
                            v-model="header.input"
                            :label="`Buscar ${header.text}...`"
                            readonly
                          ></v-text-field>
                          <v-date-picker v-model="header.input" no-title @input="menu_date = false"></v-date-picker>
                        </v-menu>
                      </v-list-tile-content>
                      <v-list-tile-avatar>
                        <v-icon @click="clearDate(index)">delete</v-icon>
                        <v-icon @click="search()">search</v-icon>
                      </v-list-tile-avatar>
                    </v-list-tile>
                  </v-list>
                </v-card>
              </v-menu>
            </v-flex>
          </th>
        </tr>
      </template>
      <template slot="items"  slot-scope="props">
        <td class="text-xs-left">{{ props.item.PadNombres }}</td>
        <td class="text-xs-left">{{ props.item.PadNombres2do }}</td>
        <td class="text-xs-left">{{ props.item.PadPaterno }}</td>
        <td class="text-xs-left">{{ props.item.PadMaterno }}</td>
        <td class="text-xs-left">{{ props.item.padapellidocasada }}</td>
        <td class="text-xs-left">{{ props.item.padcedulaidentidad }}</td>
        <td class="text-xs-left">{{ props.item.padexpcedula }}</td>
        <td class="text-xs-left">{{ props.item.padtipo }}</td>
        <td class="text-xs-left">{{ props.item.TipoPrestamo }}</td>
        <td class="text-xs-left">{{ props.item.PresFechaPrestamo | formatDate }}</td>
        <td class="text-xs-left">{{ props.item.PresMontoSol }}</td>
        <td class="text-xs-left">{{ props.item.PresEncMntAut }}</td>
        <td class="text-xs-left">{{ props.item.PresNumero }}</td>
      </template>
    </v-data-table>
    <div class="text-xs-center">
      <v-pagination
        v-model="page"
        :length="last_page"
        :total-visible="7"
        @input="next"
      ></v-pagination>
    </div>
    <div class="text-xs-right">
      <v-flex xs11 sm11 md11>
        Mostrando {{from}}-{{to}} de {{total}} registros
      </v-flex>
    </div>
  </v-card>
</template>

<script>
require('jspdf-autotable');
export default {
  data () {
    return {
      dialog: false,
      menu_date: false,
      pagination: {
        sortBy: 'PresNumero'
      },
      headers: [
        { text: '1erNombre', value: 'PrimerNombre',input:'' , menu:false,type:"text"},
        { text: '2doNombre', value: 'SegundoNombre',input:'' , menu:false,type:"text"},
        { text: 'Paterno', value: 'Paterno',input:'', menu:false ,type:"text"},
        { text: 'Materno', value: 'Materno' ,input:'', menu:false,type:"text"},
        { text: 'ApCasada', value: 'ApCasada' ,input:'', menu:false,type:"text"},
        { text: 'CI', value: 'ci',input:'' , menu:false,type:"text"},
        { text: 'Exp', value: 'padexpcedula',input:'' , menu:false,type:"text"},
        { text: 'TipoBeneficiario', value: 'padtipo',input:'' , menu:false,type:"text"},
        { text: 'TipoPrestamo', value: 'prddsc',input:'' , menu:false,type:"text"},
        { text: 'FechaSolicitud', value: 'PresFechaPrestamo',input:'', menu:false,type:"date"},
        { text: 'Monto',value:'PresMontoSol',input:'', menu:false,type:"text"},
        { text: 'MontoAutorizado',value:'PresEncMntAut',input:'', menu:false,type:"text"},
        { text: 'Nro Prestamo',value:'PresNumero',input:'', menu:false,type:"text"},
      ],
      loans: [],
      loading: true,
      last_page:1,
      total:0,
      from:0,
      to:0,
      page:1,
      paginationRows: 10,
      pagination_select:[10,20,30],
    }
  },
  mounted()
  {
    this.search();
  },
  methods:{
    async search() {
      let data = await this.getData('/api/financial',this.getParams())
      this.loans = data.data;
      this.last_page = data.last_page;
      this.total = data.total;
      this.from = data.from;
      this.to = data.to;
    },
    async getData(url, parameters) {
      try {
        this.loading = true;
        let res = await axios.get(url, { params:parameters })
        this.loading = false;
        return res.data
      } catch (e) {
        this.loading = false;
        console.log(e)
      }
    },
    next(page){
      this.page = page;
      this.search();
    },
    closeDate(index){
      this.headers[index].menu = false;
      this.search();
    },
    clearDate(index){
      this.headers[index].input='';
      this.search();
    },
    getParams(){
      let params={};
      this.headers.forEach(element => {
        params[element.value] = element.input.toUpperCase();
      });
      params['page']=this.page;
      params['pagination_rows']=this.paginationRows;
      return params;
    },
    checkInput(search)
    {
      if (search=='')
      {
        this.search();
      }
    },
    download() {
      console.log('download')
    },
  },
  filters: {
    formatDate(date) {
      const [formattedDate, time] = date.split(' ')
      const [year, month, day] = formattedDate.split('-')
      return `${month}/${day}/${year}`
    }
  }
}
</script>
