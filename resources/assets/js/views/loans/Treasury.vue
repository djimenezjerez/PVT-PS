<template>
  <v-card>
    <v-card-title>
      Tesorería
      <v-btn icon  @click="download" :disabled="!selected.length">
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
        <v-select
          :items="['TODO', 10, 20, 30]"
          v-model="paginationRows"
          @change="search()"
          label="Mostrar Registros"
        ></v-select>
      </v-flex>
    </v-card-title>
    <v-data-table
      v-model="selected"
      :headers="headers"
      :items="loans"
      hide-actions
      select-all
    >
      <template slot="headers" slot-scope="props" >
        <tr>
          <th>
            <v-checkbox
              :input-value="props.all"
              :indeterminate="props.indeterminate"
              primary
              hide-details
              @click.stop="toggleAll"
            ></v-checkbox>
          </th>
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
        <tr @click="toggleSelected(props)" :class="props.item.Account.length != 14 ? 'red white--text' : ''">
          <td>
            <v-checkbox
              :input-value="props.selected"
              primary
              hide-details
              :disabled="props.item.Account.length != 14"
            ></v-checkbox>
          </td>
          <td class="text-xs-left">{{ props.item.PadNombres }}</td>
          <td class="text-xs-left">{{ props.item.PadNombres2do }}</td>
          <td class="text-xs-left">{{ props.item.PadPaterno }}</td>
          <td class="text-xs-left">{{ props.item.PadMaterno }}</td>
          <td class="text-xs-left">{{ props.item.padapellidocasada }}</td>
          <td class="text-xs-left">{{ props.item.Account }}</td>
          <td class="text-xs-left">{{ props.item.padcedulaidentidad }}</td>
          <td class="text-xs-left">{{ props.item.padexpcedula }}</td>
          <td class="text-xs-left">{{ props.item.padtipo }}</td>
          <td class="text-xs-left">{{ props.item.TipoPrestamo }}</td>
          <td class="text-xs-left">{{ props.item.PresFechaPrestamo | formatDate }}</td>
          <td class="text-xs-left">{{ props.item.PresMontoSol | money }}</td>
          <td class="text-xs-left">{{ props.item.PresEncMntAut | money }}</td>
          <td class="text-xs-left">{{ props.item.PresNumero }}</td>
        </tr>
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
export default {
  data () {
    return {
      dialog: false,
      menu_date: false,
      pagination: {
        sortBy: 'PresNumero'
      },
      headers: [
        { text: '1er Nombre', value: 'PrimerNombre',input:'' , menu:false,type:"text"},
        { text: '2do Nombre', value: 'SegundoNombre',input:'' , menu:false,type:"text"},
        { text: 'Paterno', value: 'Paterno',input:'', menu:false ,type:"text"},
        { text: 'Materno', value: 'Materno' ,input:'', menu:false,type:"text"},
        { text: 'ApCasada', value: 'ApCasada' ,input:'', menu:false,type:"text"},
        { text: 'Nro Cuenta', value: 'Account' ,input:'', menu:false,type:"text"},
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
      paginationRows: 'TODO',
      selected: []
    }
  },
  mounted()
  {
    this.search()
  },
  methods:{
    async search() {
      let data = await this.getData('/api/treasury',this.getParams())
      this.loans = data.data
      console.log(this.loans)
      this.last_page = data.last_page
      this.total = data.total
      this.from = data.from
      this.to = data.to
    },
    async getData(url, parameters) {
      try {
        this.loading = true
        let res = await axios.get(url, { params:parameters })
        this.loading = false
        return res.data
      } catch (e) {
        this.loading = false
        console.log(e)
      }
    },
    next(page){
      this.page = page
      this.search()
    },
    closeDate(index){
      this.headers[index].menu = false
      this.search()
    },
    clearDate(index){
      this.headers[index].input=''
      this.search()
    },
    getParams(){
      let params={}
      this.headers.forEach(element => {
        params[element.value] = element.input.toUpperCase()
      })
      params['page']=this.page
      if (this.paginationRows == 'TODO') {
        params['pagination_rows']=1000
      } else {
        params['pagination_rows']=this.paginationRows
      }
      return params
    },
    checkInput(search)
    {
      if (search=='')
      {
        this.search()
      }
    },
    toggleAll () {
      if (this.selected.length) this.selected = []
      else this.selected = this.loans.filter(o => o.Account.length == 14)
    },
    toggleSelected(props) {
      if (props.item.Account.length == 14) props.selected = !props.selected
    },
    async download() {
      try {
        this.loading = true
        let res = await axios.get('/api/treasury', {
          params: {
            txt: true,
            ids: this.selected.map(o => parseInt(o.id))
          }
        })
        const blob = new Blob([res.data], {
          type: res.headers["content-type"]
        })
        let link = document.createElement("a")
        link.href = window.URL.createObjectURL(blob)
        let fileName = `desembolsos_${new Date().toString().split('GMT')[0]}`
        const contentDisposition = res.headers["content-disposition"]
        if (contentDisposition) {
          const fileNameMatch = contentDisposition.match(/filename="(.+)"/)
          if (fileNameMatch.length === 2) {
            fileName = fileNameMatch[1]
          }
        }
        link.download = fileName
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        this.loading = false
      } catch (e) {
        this.loading = false
        console.log(e)
      }
    },
  },
  filters: {
    formatDate(date) {
      const [formattedDate, time] = date.split(' ')
      const [year, month, day] = formattedDate.split('-')
      return `${month}/${day}/${year}`
    },
    money(value) {
      return parseFloat(value).toFixed(2)
    }
  }
}
</script>