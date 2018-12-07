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
            <td class="text-xs-left"> <a  v-bind:href="generate_link(props.item.IdPrestamo)"><v-icon>assignment</v-icon></a> 
                <v-btn icon @click="makePDF(props.item.IdPrestamo)"><v-icon color="info">insert_drive_file</v-icon></v-btn>
            </td>
            
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
        <br>


        <v-dialog v-model="show_certificate" max-width="800px" max-high="40px">
            <v-card>
            <v-card-title>
                <span class="headline"> PDF</span>
            </v-card-title>
        
            <v-card-text >
                <v-container grid-list-md>
                <div class="embed-container">
                    <iframe frameborder="0" width="700" height="500" :src="cadena"></iframe>
                </div>
                </v-container>
            </v-card-text>

     
            </v-card>
        </v-dialog>  

    </v-card>
</template>
<script>
import * as jsPDF from 'jspdf';
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
        pagination_select:[10,20,30],
        show_certificate:false,
        cadena:null,
        certification:{
            grado:'',
            nombre:'',
            producto:'',
            tipo:' largo plazo',
            prestamo:'',
            amoritzacion: null,
            fecha:'',
            literal:'',
            fecha_t:'',
            hoja:''
        }
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
                params[element.value] = element.input.toUpperCase();
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
        },
        makePDF(id_prestamo){
           
            axios({
                url: '/api/certificate_info?id_prestamo='+id_prestamo,
                method: 'GET',
            }).then((response) => {
                    console.log(response.data);
                    this.certification.grado = response.data.affiliate.grado;
                    this.certification.nombre = (response.data.affiliate.first_name || '') +' '+(response.data.affiliate.second_name || '' ) + ' '+(response.data.affiliate.last_name || '' )+' '+(response.data.affiliate.mother_last_name || '' )+' con C.I. '+(response.data.affiliate.identity_card || '' ) +' '+(response.data.affiliate.ext || '');
                    this.certification.amoritzacion = response.data.amortizacion;
                    console.log('ingresando');
                   
                   
                   var d = new jsPDF();
                    this.show_certificate = true;
                    let logo = 'data:image/jpeg;base64,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';


                    console.log('mostrando pdf hdp');
                    d.addImage(logo, 'JPEG', 10, 5);
                    let leyenda = 'LA SUSCRITA ENCARGADO DE REGISTRO, CONTROL Y RECUPERACION DE PRESTAMOS, DE LA MUTUAL DE SERVICIOS AL POLICIA "MUSERPOL", EN USO DE SUS ATRIBUCIONES, EN CUANTO PUEDE Y EL DERECHO LE PERMITE:'; 
                    let splitTitle = d.splitTextToSize(leyenda, 150);
                    d.setFontSize(10)
                    d.text(90,10,splitTitle);
                    d.text('C E R T I F I C A:',20,40);
                    let p1 = 'Que, previa revisión y verificación de la base de datos del Sistema de Prétamo de la Endidad  "SISMU", de los señores afiliados del Sector Activo, se VERIFICO que:';
                    let spl1 = d.splitTextToSize(p1,180);
                    d.text(20,50,spl1);

                    d.text('El (la) Sr (a). '+this.certification.grado+' '+this.certification.nombre ,20,60,);

                    let p2 = 'Sienedo así se encuentra en situación de mora su préstamo a '+this.certification.tipo+' con garantia '+this.certification.producto+ ' registrado con el Nro. '
                    +this.certification.prestamo+' desde '+this.certification.fecha +' y siendo su saldo según ultimo registro de pago Bs. 215135 ('+this.certification.literal+').';
                    let spl2 = d.splitTextToSize(p2,180);
                    d.text(20,70,spl2);
                    // d.text("INFORME  ",90,20);
                    this.cadena = d.output('datauristring');
            });

          
           // $('iframe').attr('src', cadena);
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
