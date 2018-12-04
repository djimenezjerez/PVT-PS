export const storage = {
    namespaced: true,
    state:{
        drawer:null
    },
    mutations:{
        updateDrawer(state,drawer){
            state.drawer = drawer;
        }
    }
};