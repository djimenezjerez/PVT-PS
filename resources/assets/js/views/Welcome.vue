,<template>
      <v-container
        fluid
        grid-list-lg
      >
        <v-layout row wrap>
          <v-flex xs8>
            <v-card>
              <v-card-title primary-title>
              <div>
                <div class="headline"> Prestamos Vigentes {{this.cantidadPrestamos}} </div>
              </div>
            </v-card-title>
              <canvas id="prestamos_tipo" ></canvas>
            </v-card>
          </v-flex>

          <v-flex xs4>
            <v-card >
                <v-list two-line>
                  <template v-for="(item, index) in items">
                    <v-subheader
                      v-if="item.header"
                      :key="item.header"
                    >
                      {{ item.header }}
                    </v-subheader>

                    <v-divider
                      v-else-if="item.divider"
                      :inset="item.inset"
                      :key="index"
                    ></v-divider>

                    <v-list-tile
                      v-else
                      :key="item.title"
                      avatar
                   
                    >
                      <v-list-tile-avatar>
                        <v-icon>{{item.icon}}</v-icon>
                      </v-list-tile-avatar>

                      <v-list-tile-content>
                        <v-list-tile-title v-html="item.title"></v-list-tile-title>
                        <v-list-tile-sub-title v-html="item.subtitle"></v-list-tile-sub-title>
                      </v-list-tile-content>
                    </v-list-tile>
                  </template>
                </v-list>
            </v-card>
          </v-flex>

          <v-flex xs6>
            <v-card >
              <v-card-title primary-title>
              <div>
                <div class="headline"> Prestamos por Producto </div>
              </div>
            </v-card-title>
              <canvas id="prestamos_producto" ></canvas>
            </v-card>
          </v-flex>
          <v-flex xs6>
            <v-card >
              <v-card-title primary-title>
              <div>
                <div class="headline"> Prestamos  </div>
              </div>
            </v-card-title>
              
            </v-card>
          </v-flex>
        </v-layout>
      </v-container>
  

</template>
<script>
export default {
 
  data(){
    return {
        prestamosTipo:null,
        prestamosProducto:null,
        prestamos:null,
        items: []
      }
    }
  ,
  methods:{
    createChart(chartId, chartData) {
      const ctx = document.getElementById(chartId);
      const myChart = new Chart(ctx, {
        type: chartData.type,
        data: chartData.data,
        options: chartData.options,
      });
    },
    prestamosRender()
    {
      let labels = [];
      let data =[];
      let total;
      this.prestamosTipo.forEach(item => {
        labels.push(item.nombre);
        data.push(item.cantidad);
        total+=item.cantidad;
      });
      let datasets = [{
        label: 'Total '+total,
        data: data,
        backgroundColor: [
            'rgba(255, 206, 86, 1)',
            'rgba(54, 162, 235, 1)',
        ],
        borderWidth: 1
      }];
      // console.log({labels,datasets}); 
      return {type: 'pie',data:{labels, datasets}};
    },
    productData(){
      let labels = [];
      let data =[];
      this.prestamosProducto.forEach(item => {
        labels.push(item.nombre.trim());
        data.push(item.cantidad);
      });
      let datasets = [{
        label: 'Vigentes' ,
        data: data,
        backgroundColor: [
        "#f1c40f",
        "#e74c3c",
        "#3498db",
        "#2ecc71",
        "#9b59b6",
        "#34495e",
        "#95a5a6",
        "#FF6384",
        "#1AB394",
        "#FFA365",

        ],
        hoverBackgroundColor: [
        "#f1c40f",
        "#e74c3c",
        "#3498db",
        "#2ecc71",
        "#9b59b6",
        "#34495e",
        "#95a5a6",
        "#FF6384",
        "#1AB394",
        "#FFA365" 
        ],
        borderWidth: 1
      }];
      return {type: 'pie',data:{labels, datasets}};
    },
    getData(url,parameters){
        return new Promise((resolve,reject)=>{
            this.loading = true;
            axios.get(url)
                .then((response) => {
                    this.loading = false;
                    resolve(response.data);
                });
        });
    },
    load(){
          
        return new Promise((resolve,reject)=>{   
            this.getData('api/reporte').then((data)=>{
                
                this.prestamosTipo = data.prestamos_tipo;
                this.prestamosProducto = data.prestamos_producto;
            
                resolve();
            });
        });
    }
    
  },
  computed:{
    cantidadPrestamos(){
      let total=0;
      if(this.prestamosTipo)
      {
        this.prestamosTipo.forEach(item=>{ total+=item.cantidad });
      }
      return numeral(total).format('0,0.00');
    },
    totalPrestamos(){
      let total=0;
      if(this.prestamosTipo)
      {
        this.prestamosTipo.forEach(item=>{ total+=item.sub_total });
      }
      return numeral(total).format('0,0.00');
    },
    getAmountActivo(){
      let amount=0;

      if(this.prestamosTipo)
      {
        amount= this.prestamosTipo[1].sub_total;
      }
      return numeral(amount).format('0,0.00');
    },
    getAmountPasivo(){
      let amount=0;

      if(this.prestamosTipo)
      {
        amount= this.prestamosTipo[0].sub_total;
      }
      return numeral(amount).format('0,0.00');
    }
  },
  mounted(){
    
    this.load().then(()=>{
        this.createChart('prestamos_tipo',this.prestamosRender());
        this.createChart('prestamos_producto',this.productData());
        this.items = [
          { header: 'Prestamos Vigentes' },
          {
            icon: 'fa-user',
            title: 'Bs '+this.getAmountActivo,
            subtitle: 'Activo'
          },
          { divider: true, inset: true },
          {
            icon: 'fa-user',
            title: 'Bs '+this.getAmountPasivo,
            subtitle:  'Pasivo'
          },
          { divider: true, inset: true },
          {
            icon: 'fa-money',
            title: 'Bs '+this.totalPrestamos,
            subtitle: 'Total',
          }
        ]
    });
    
  }
}
</script>
