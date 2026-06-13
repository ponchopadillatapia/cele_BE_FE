import { createApp } from 'vue';
import RegisterForm from './components/RegisterForm.vue';
import '../css/app.css';

const app = createApp(RegisterForm);
app.mount('#app');
