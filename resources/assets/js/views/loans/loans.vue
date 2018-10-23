<template>
     <v-card>
        <v-card-title>
        Prestamos
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

                                            <!-- <v-divider></v-divider>

                                     
                                            <v-card-actions>
                                            <v-spacer></v-spacer>

                                            <v-btn flat @click="header.menu = false">Cancel</v-btn>
                                            <v-btn color="primary" flat @click="header.menu">Save</v-btn>
                                            </v-card-actions> -->
                                            <!-- <v-menu
                                                
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
                                                outline
                                                slot="activator"
                                                v-model="header.input"
                                                label="Date"
                                            ></v-text-field>
                                            <v-date-picker v-model="header.input" no-title @input="menu_date = false"></v-date-picker>
                                            <v-btn icon >
                                                <v-icon>search</v-icon>
                                            </v-btn>
                                            </v-menu>                                   -->
                                        <!-- <v-text-field 
                                            outline
                                            hide-details
                                            v-model="header.input"
                                            append-icon="search"
                                            :label="`Buscar ${header.text}...`"
                                            @keydown.enter="search()"
                                            @keyup.delete="checkInput(header.input)"
                                            @keyup.esc="header.menu=false"
                                        ></v-text-field> -->
                                        <!-- <v-date-picker v-model="header.input" no-title @input="closeDate(index)" ></v-date-picker> -->
                                    
                                    </v-card>
                            </v-menu>
                            <!-- <v-icon small @click="toggleOrder(header.value)" v-if="header.value == filterName ">{{pagination.descending==false?'arrow_upward':'arrow_downward'}}</v-icon> -->
                        </v-flex>
                </th>
           </tr>
        </template>
        <template slot="items"  slot-scope="props">
            <td class="text-xs-left">{{ props.item.PresNumero }}</td>
            <td class="text-xs-left">{{ props.item.PresFechaDesembolso }}</td>
            <td class="text-xs-left">{{ props.item.PadTipo }}</td>
            <td class="text-xs-left">{{ props.item.PadMatricula }}</td>
            <td class="text-xs-left">{{ props.item.PadMatriculaTit }}</td>
            <td class="text-xs-left">{{ props.item.PadCedulaIdentidad }}</td>
            <td class="text-xs-left">{{ props.item.PadExpCedula }}</td>
            <td class="text-xs-left">{{ props.item.PadNombres }}</td>
            <td class="text-xs-left">{{ props.item.PadNombres2do }}</td>
            <td class="text-xs-left">{{ props.item.PadPaterno }}</td>
            <td class="text-xs-left">{{ props.item.PadMaterno }}</td>
           
            <td class="text-xs-left">{{ props.item.PresCtbNroCpte }}</td>
            <td class="text-xs-left"> <a  v-bind:href="generate_link(props.item.IdPrestamo)"><v-icon>assignment</v-icon></a> </td>
            
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
        menu_date: false,
        // date:false,
        pagination: {
          sortBy: 'PresNumero'
        },
        headers: [
            { text: 'Nro Prestamo', value: 'PresNumero',input:'' , menu:false,type:"text"},
            { text: 'Fecha Desembolso', value: 'PresFechaDesembolso',input:'' , menu:false,type:"date"},
            { text: 'Tipo', value: 'PadTipo',input:'', menu:false ,type:"text"},
            { text: 'Matricula', value: 'PadMatricula' ,input:'', menu:false,type:"text"},
            { text: 'Matricula Titular', value: 'PadMatriculaTit' ,input:'', menu:false,type:"text"},
            { text: 'CI', value: 'PadCedulaIdentidad',input:'' , menu:false,type:"text"},
            { text: 'Exp', value: 'PadExpCedula',input:'' , menu:false,type:"text"},
            { text: '1er Nombre', value: 'PadNombres',input:'' , menu:false,type:"text"},
            { text: '2do Nombre', value: 'PadNombres2do',input:'' , menu:false,type:"text"},
            { text: 'Ap. Paterno', value: 'PadPaterno',input:'', menu:false,type:"text"},
            { text: 'Ap. Materno',value:'PadMaterno',input:'', menu:false,type:"text"},
            { text: 'Nro Comprobante',value:'PresCtbNroCpte',input:'', menu:false,type:"text"},
            { text: 'Accion',value:'actions',input:'', menu:false,type:"text"},
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
                this.getData('/api/loans',this.getParams()).then((data)=>{
                    this.amortizations = data.data;
                    this.last_page = data.last_page;
                    this.total = data.total;
                    this.from = data.from;
                    this.to = data.to;
                    // this.page = 1   ;
                    resolve();
                });
            });
        },
        closeDate(index){
            this.headers[index].menu = false;
            this.search();
            // console.log(this.headers[index]);
            
        },
        clearDate(index){
            this.headers[index].input='';
            this.search();
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
                url: '/api/loans',
                method: 'GET',
                params: parameters,
                responseType: 'blob', // important
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Prestamos'+moment().format()+'.xls');
                document.body.appendChild(link);
                link.click();
                self.dialog = false;
            });
        },
        generate_link(id){
            return 'http://sismu.muserpol.gob.bo/musepol/akardex.aspx?'+id;
            //console.log(this.loans)
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
