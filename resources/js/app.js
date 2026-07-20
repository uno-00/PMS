import './bootstrap';
import './rich-text-editor';
import './charts';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);
window.Chart = Chart;

// Alpine is bundled and started by Livewire (@livewireScripts). Do not
// import alpinejs or call Alpine.start() here — that causes "Detected
// multiple instances of Alpine running" in the browser console.
