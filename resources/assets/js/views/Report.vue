<template>
  <div>
    <v-btn small color="success" @click="download" 
    :disabled="dialog"
    :loading="dialog"
    >
    <v-icon>sim_card_alert</v-icon>
      Irregulares
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
  </div>
  
</template>
<script>
export default {
     data() {
            return {
            
            dialog: false,
          
            }
    },
    // define methods under the `methods` object
    methods: {
      download: function (event) {
        // `this` inside methods point to the Vue instance
        self = this;
        self.dialog = true
      //  self.dialog = true;
      axios({
          url: '/api/reporte_prestamos',
          method: 'GET',
          responseType: 'blob', // important
        }).then((response) => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'prestamos_irregulares.xls');
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
