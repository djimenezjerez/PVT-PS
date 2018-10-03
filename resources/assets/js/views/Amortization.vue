<template>
     <v-card>
        <v-card-title>
        Amortizaciones
        <v-btn icon  @click="download">
            <v-icon color="green">
                fa-file-excel-o
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
        :items="amortizations"
        :pagination.sync="pagination"
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
                                    <v-card  >
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
                            </v-menu>
                            <!-- <v-icon small @click="toggleOrder(header.value)" v-if="header.value == filterName ">{{pagination.descending==false?'arrow_upward':'arrow_downward'}}</v-icon> -->
                        </v-flex>
                </th>
           </tr>
        </template>
        <template slot="items"  slot-scope="props">
            <td class="text-xs-left">{{ props.item.PresNumero }}</td>
            <td class="text-xs-left">{{ props.item.AmrFecPag }}</td>
            <!-- <td class="text-xs-left">{{ props.item.PadTipo }}</td> -->
            <!-- <td class="text-xs-left">{{ props.item.PadMatricula }}</td> -->
            <td class="text-xs-left">{{ props.item.PadCedulaIdentidad }}</td>
            <td class="text-xs-left">{{ props.item.PadNombres }}</td>
            <td class="text-xs-left">{{ props.item.PadNombres2do }}</td>
            <td class="text-xs-left">{{ props.item.PadPaterno }}</td>
            <td class="text-xs-left">{{ props.item.PadMaterno }}</td>
            <td class="text-xs-left">{{ props.item.AmrTotPag }}</td>
            <td class="text-xs-left">{{ props.item.AmrNroCpte }}</td>
            <!-- <td class="justify-center layout px-0">
                <v-icon
                    small
                    class="mr-2"
                    @click="editItem(props.item)"
                >
                    edit
                </v-icon>
                <v-icon
                    small
                    @click="deleteItem(props.item)"
                >
                    delete
                </v-icon>
            </td> -->
        </template>
       
phone
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
        <br>
    </v-card>
</template>
<script>
export default {
    data () {
      return {
        dialog: false,
        pagination: {
          sortBy: 'PresNumero'
        },
        headers: [
            { text: 'Nro Prestamo', value: 'PresNumero',input:'' , menu:false},
            { text: 'Fecha Pago', value: 'AmrFecPag',input:'' , menu:false},
            // { text: 'Tipo', value: 'PadTipo' , menu:false},
            // { text: 'Matricula', value: 'PadMatricula' , menu:false},
            { text: 'CI', value: 'PadCedulaIdentidad',input:'' , menu:false},
            { text: '1er Nombre', value: 'PadNombres',input:'' , menu:false},
            { text: '2do Nombre', value: 'PadNombres2do',input:'' , menu:false},
            { text: 'Ap. Paterno', value: 'PadPaterno',input:'', menu:false},
            { text: 'Ap. Materno',value:'PadMaterno',input:'', menu:false},
            { text: 'Total Pagado',value:'AmrTotPag',input:'', menu:false},
            { text: 'Nro Comprobante',value:'AmrNroCpte',input:'', menu:false},
        ],
        amortizations: [],
        loading: true,
        last_page:1,
        total:0,
        from:0,
        to:0,
        page:1,
        paginationRows: 10,
        pagination_select:[10,20,30]
      }
    },
    mounted()
    {
        this.search();
    },
    created(){
        //   axios.get('/api/amortizacion/create')
        //         .then((response) => {                                       
        //             this.newProvider = response.data.amortizacion; 
        //             this.newContact = response.data.contact; 
        //         });    
    },
    methods:{
        
        getData(url,parameters){
            return new Promise((resolve,reject)=>{
               this.loading = true;
               axios.get(url,{
                        params:parameters
                    })
                    .then((response) => {
                        this.loading = false;
                        resolve(response.data);
                    });
            });
        },
        next(page){
            this.page = page;
            this.search();
        },
        search(){
            
            return new Promise((resolve,reject)=>{   
                this.getData('/api/amortizacion',this.getParams()).then((data)=>{
                    this.amortizations = data.data;
                    this.last_page = data.last_page;
                    this.total = data.total;
                    this.from = data.from;
                    this.to = data.to;
                    // this.page = data.first_page;
                    resolve();
                });
            });
        },
        getParams(){
            let params={};
            this.headers.forEach(element => {
                params[element.value] = element.input;
            });
            // params['sorted']=this.filterName;
            // params['order']=this.pagination.descending==true?'asc':'desc';
            params['page']=this.page;
            params['pagination_rows']=this.paginationRows;
            return params;
        },
        checkInput(search)
        {
            if(search=='')
            {
                this.search();
            }
        },
        download: function (event) {
            // `this` inside methods point to the Vue instance
            self = this;
            self.dialog = true
            //  self.dialog = true;
            let parameters = this.getParams();
            parameters.excel =true;
            console.log(parameters);
            axios({
                url: '/api/amortizacion',
                method: 'GET',
                params: parameters,
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Amortizaciones.xls');
                document.body.appendChild(link);
                link.click();
                self.dialog = false;
            });
        }
        // toggleOrder (filter) {
        //     this.filterName = filter;
        //     this.search(filter).then(()=>{
        //         this.pagination.sortBy = this.filterName; 
        //         this.pagination.descending = !this.pagination.descending
        //     });    
        // },
       
      
    },
    watch: {
        filterValue (fv) {      
            if (fv =='') {
                this.search();
            }
        }
    },
    computed:{
      
    }
}
</script>
