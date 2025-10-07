import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/client.css', 
                'resources/js/app.js', 
                'resources/js/client.js', 
                'resources/js/firebase-service.js',
                'resources/js/dashboard-charts.js',
                'resources/js/dashboard-controller.js',
                'resources/js/activity-log.js',
                'resources/js/booking-requests.js',
                'resources/js/business-analytics.js',
                'resources/js/chatbot.js',
                'resources/js/reports-management.js',
                'resources/js/room-management.js',
                'resources/js/room-billing.js',
                'resources/js/settings.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
