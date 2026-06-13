import { createApp } from 'vue';
import LoginForm from './components/LoginForm.vue';
import '../css/app.css';

const app = createApp(LoginForm);
app.mount('#app');
