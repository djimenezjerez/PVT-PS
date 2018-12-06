let user ;
try {
    user =  JSON.parse(localStorage.getItem('user'));
  }
  catch(error) {
    console.error(error);
    user = {};
    // expected output: SyntaxError: unterminated string literal
    // Note - error messages will vary depending on browser
  }
  
export const autentication = {
    namespaced: true,
    state:{
        status: '',
        token: localStorage.getItem('token') || '',
        user : user 
    },
    mutations: {
        auth_request(state){
          state.status = 'loading';
          console.log('cambiando estado');
        },
        auth_success(state, {token, user}){
          state.status = 'success'
          state.token = token
          state.user = user
          console.log('seteando token XD ');
          console.log(token);
          console.log('seteando user ');
          console.log(user);
        },
        auth_error(state){
          state.status = 'error'
        },
        logout(state){
          state.status = ''
          state.token = ''
        },
    },
    actions:{
        login({commit}, user){
            return new Promise((resolve, reject) => {
              commit('auth_request')
              axios({url: 'api/login', data: user, method: 'POST' })
              .then(resp => {
                console.log(resp.data.user);
                const token = resp.data.token
                const user = resp.data.user
                console.log('imprimiendo variable');
                console.log(user);
                localStorage.setItem('token', token)
                localStorage.setItem('user',JSON.stringify(user))//te amo nadia
                axios.defaults.headers.common['Authorization'] = 'Bearer '+token // para todas las consultas axios XD
                commit('auth_success', {token, user});
                resolve(resp)
              })
              .catch(err => {
                commit('auth_error')
                localStorage.removeItem('token')
                reject(err)
              })
            })
        },
        logout({commit}){
            return new Promise((resolve, reject) => {
                commit('logout')
                localStorage.removeItem('token')
                delete axios.defaults.headers.common['Authorization']
                resolve()
            })
        }
    },
    getters : {
        isLoggedIn: state => !!state.token,
        authStatus: state => state.status,
        userLoged: state=> state.user
    }
   
};