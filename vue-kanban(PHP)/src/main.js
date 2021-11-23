import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import init from './init.js'

const app = createApp(App)
init(app)
app.use(router).mount('#app')
