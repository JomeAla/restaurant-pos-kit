import './bootstrap';
import '../css/app.css';
import { createRoot } from 'react-dom/client';
import './i18n';
import App from './components/AppRouter';

createRoot(document.getElementById('app')).render(<App />);

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
