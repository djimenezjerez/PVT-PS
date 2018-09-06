<template>
  <div>
    <v-btn small color="success" @click="download" >Descargar</v-btn>
  </div>
  
</template>
<script>
export default {
    data: {
    name: 'Vue.js'
    },
    // define methods under the `methods` object
    methods: {
      download: function (event) {
        // `this` inside methods point to the Vue instance
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
        });
      }
    }
}
</script>
