<template>
 <v-layout row>
  <v-flex xs6 sm6 >
    <v-card>
        <v-card-title primary-title>
          <div>
            <div class="headline">Prestamos Vigentes</div>
            <span class="grey--text"></span>
            
          </div>
          <canvas id="ejemplohdp"></canvas>
        </v-card-title>
 
    </v-card>
  </v-flex>
 </v-layout>

</template>
<script>
export default {
 
  data(){
    return {
      charData:{
        type: 'pie',
        data: {
            labels: ["Activo", "Pasivo"],
            datasets: [{
                label: '# of Votes',
                data: [50, 16],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        }
      }
    }
  },
  methods:{
    createChart(chartId, chartData) {
    const ctx = document.getElementById(chartId);
    const myChart = new Chart(ctx, {
      type: chartData.type,
      data: chartData.data,
      options: chartData.options,
    });
  }
  },
  mounted(){
     this.createChart('ejemplohdp', this.charData);
  }
}
</script>
