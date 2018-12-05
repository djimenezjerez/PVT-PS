<template>
    <v-content>
      <v-container fluid fill-height>
        <v-layout align-center justify-center>
          <v-flex xs12 sm8 md4>
            <v-form @submit.prevent="login">
            <v-card >
              <v-toolbar dark color="primary">
                <v-toolbar-title>Sistema de Soporte</v-toolbar-title>
                <v-spacer></v-spacer>
                
              </v-toolbar>
              <v-card-text>
                <div class="owl">
                      <div :class="owl_hide?'hand password':'hand'"></div>
                      <div :class="owl_hide?'hand hand-r password':'hand hand-r'"></div>
                      <div class="arms">
                        <div :class="owl_hide?'arm password':'arm'"></div>
                        <div :class="owl_hide?'arm arm-r password':'arm arm-r'"></div>
                      </div>
                </div>
                <v-form>
                  <v-text-field prepend-icon="person" v-model="username" label="Usuario" type="text"></v-text-field>
                  <v-text-field prepend-icon="lock" v-model="password" label="Contraseña" type="password" @focus="owl_hide = true"  @blur="owl_hide = false" ></v-text-field>
                </v-form>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" type="submit">Inciar Sesion</v-btn>
              </v-card-actions>
            </v-card>
            </v-form>
          </v-flex>
        </v-layout>
      </v-container>
    </v-content>
</template>
<script>


export default {
  data() {
    return {
      owl_hide: false,
      username : "",
      password : ""
    }
  },
  methods:{
        login() {
        console.log('enviando datos auth');
        let username = this.username 
        let password = this.password
        console.log({ username, password });
          this.$store.dispatch('auth/login', { username, password })
          .then(() => {
              console.log("autenticado")
              this.$router.push('/')
              })
          .catch(err => console.log(err))
        }
  }
}
</script>

<style>
.owl {
  margin: auto;
  width: 211px;
  height: 108px;
  background-image: url("https://dash.readme.io/img/owl-login.png");
  position: relative;
}
.owl .hand {
  width: 34px;
  height: 34px;
  border-radius: 40px;
  background-color: #472d20;
  transform: scaleY(0.6);
  position: absolute;
  left: 14px;
  bottom: -8px;
  transition: 0.3s ease-out;
}
.owl .hand.password {
  transform: translateX(42px) translateY(-15px) scale(0.7);
}
.owl .hand.hand-r {
  left: 170px;
}
.owl .hand.hand-r.password {
  transform: translateX(-42px) translateY(-15px) scale(0.7);
}
.owl .arms {
  position: absolute;
  top: 58px;
  height: 41px;
  width: 100%;
  overflow: hidden;
}
.owl .arms .arm {
  width: 40px;
  height: 65px;
  background-image: url("https://dash.readme.io/img/owl-login-arm.png");
  position: absolute;
  left: 20px;
  top: 40px;
  transition: 0.3s ease-out;
}
.owl .arms .arm.password {
  transform: translateX(40px) translateY(-40px);
}
.owl .arms .arm.arm-r {
  left: 158px;
  transform: scaleX(-1);
}
.owl .arms .arm.arm-r.password {
  transform: translateX(-40px) translateY(-40px) scaleX(-1);
}
</style>
