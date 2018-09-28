<template>
     <v-card>
        <v-card-title>
        Amortizaciones
        <v-spacer></v-spacer>
        <!-- <v-dialog v-model="dialog" max-width="800px" max-high="40px">
            <v-btn slot="activator" color="primary" dark class="mb-2">Nuevo</v-btn>
            <v-card>
            <v-card-title>
                <span class="headline">{{ formTitle }}</span>
            </v-card-title>
        
            <v-card-text v-if="newProvider">
                <v-container grid-list-md>
                <v-layout wrap>
                    <v-flex xs12 sm6 md6>
                    <v-text-field v-model="newProvider.name" label="Nro"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm6 md6>
                    <v-text-field v-model="newProvider.offer" label="Oferta"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm6 md12>
                    <v-text-field v-model="newProvider.direccion1" label="Direccion 1"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm6 md12>
                    <v-text-field v-model="newProvider.direccion2" label="Direccion 2"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm6 md12>
                    <v-text-field v-model="newProvider.city" label="Ciudad"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm6 md6>
                    <v-text-field v-model="newProvider.balance" label="Balance"></v-text-field>
                    </v-flex>
                    <v-flex xs12 sm6 md6>
                    <v-text-field v-model="newProvider.debit" label="Debito"></v-text-field>
                    </v-flex>
                </v-layout>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" flat @click.native="close">Cancelar</v-btn>
                <v-btn color="blue darken-1" flat @click.native="save">Guardar</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>         -->
        </v-card-title>
        <v-data-table
        :headers="headers"
        :items="providers"
        :pagination.sync="pagination"
        hide-actions
        >
        <template slot="headers" slot-scope="props" >
           <tr>
                <th v-for="(header,index) in props.headers" :key="index" class="text-xs-left">
                    
                        <v-flex v-if="header.value!='actions'">
                            <span @click="toggleOrder(header.value)">{{ header.text }}
                                
                            </span>
                            <v-menu 
                                    :close-on-content-click="false"
                                    >
                                    <v-btn
                                        slot="activator"
                                        icon
                                        v-if="header.sortable!=false"
                                    >
                                    <v-icon  small>fa-filter</v-icon>
                                    </v-btn>
                                    <v-card  >
                                        <v-text-field
                                         outline
                                         hide-details
                                        v-model="header.input"
                                        append-icon="search"
                                        :label="`Buscar ${header.text}...`"
                                       
                                        @keydown.enter="search(header.value)"
                                    ></v-text-field>
                                    
                                    </v-card>
                            </v-menu>
                            <v-icon small @click="toggleOrder(header.value)" v-if="header.value == filterName ">{{pagination.descending==false?'arrow_upward':'arrow_downward'}}</v-icon>
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
            { text: 'Nro Prestamo', value: 'PresNumero',input:'' },
            { text: 'Fecha Pago', value: 'AmrFecPag',input:'' },
            // { text: 'Tipo', value: 'PadTipo' },
            // { text: 'Matricula', value: 'PadMatricula' },
            { text: 'CI', value: 'PadCedulaIdentidad',input:'' },
            { text: '1er Nombre', value: 'PadNombres',input:'' },
            { text: '2do Nombre', value: 'PadNombres2do',input:'' },
            { text: 'Ap. Paterno', value: 'PadPaterno',input:''},
            { text: 'Ap. Materno',value:'PadMaterno',input:''},
            { text: 'Total Pagado',value:'AmrTotPag',input:''},
            { text: 'Nro Comprobneee',value:'AmrNroCpte',input:''},
        ],
        providers: [],
        loading: true,
        filterName: 'name',
        filterValue: '',
        newProvider: null,
        newContacts:[],
        newContact: null,
        editedItem: -1,
        page:1,
        last_page:1,
        order: true
      }
    },
    mounted()
    {
        this.search('PresNumero');
        // this.getData('/api/amortizacion',this.getParams())
        //     .then((data)=>{
        //         this.providers = data.data;
        //         this.last_page = data.last_page;
        //     });
    },
    created(){
        //   axios.get('/api/amortizacion/create')
        //         .then((response) => {                                       
        //             this.newProvider = response.data.amortizacion; 
        //             this.newContact = response.data.contact; 
        //         });    
    },
    methods:{
        
        getItems(url){
            return new Promise((resolve,reject)=>{
               this.loading = true;
               axios.get(url)
                    .then((response) => {
                        this.loading = false;
                        resolve(response.data);
                    });
            });
        },
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
            // console.log(page);
            this.getItems('/api/amortizacion?page='+page+'&search='+this.filterValue+'&sorted='+this.filterName+'&order=asc').then((data)=>{
                this.providers = data.data;
                this.last_page = data.last_page;
            });
        },
        search(filter){
            
            this.filterName = filter;
            return new Promise((resolve,reject)=>{   
                this.getData('/api/amortizacion',this.getParams()).then((data)=>{
                    this.providers = data.data;
                    this.last_page = data.last_page;
                    resolve();
                });
            });
        },
        getParams(){
            let params={};
            this.headers.forEach(element => {
                params[element.value] = element.input;
            });
            let orderBy = this.pagination.descending==true?'asc':'desc';
            params['sorted']=this.filterName;
            params['order']=orderBy;
            // console.log(params);
            return params;
        },
        toggleOrder (filter) {
            this.search(filter).then(()=>{
                this.pagination.sortBy = filter; 
                this.pagination.descending = !this.pagination.descending
            });    
        },
       
        editItem (item) {
            this.editedIndex = this.providers.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },
        save () {
            // if (this.editedIndex > -1) {
            // Object.assign(this.providers[this.editedIndex], this.editedItem)
            // } else {
                this.providers.push(this.editedItem)
            // }
            this.close()
        }
    },
    watch: {
        filterValue (fv) {      
            if (fv =='') {
                this.search('PresNumero');
            }
        }
    },
    computed:{
        formTitle () {
            return this.editedIndex === -1 ? 'Nuevo Proveedor' : 'Editar Proveedor'
        }
    }
}
</script>
