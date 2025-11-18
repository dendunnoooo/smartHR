
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export const paths = [
   'Modules/Whiteboard/resources/apps/TlDraw/css/index.css',
   'Modules/Whiteboard/resources/apps/TlDraw/main.jsx',
   'Modules/Whiteboard/resources/apps/ExcaliDraw/app.scss',
   'Modules/Whiteboard/resources/apps/ExcaliDraw/main.jsx',
];

export default defineConfig({
    build: {
        outDir: path.resolve(__dirname, '../../public/build-whiteboard'),
        emptyOutDir: true,
        manifest: true,
        manifestFileName: 'manifest.json',
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-whiteboard',
            input: paths,
            refresh: true,
        }),
        react()
    ],
    server: {
        watch: {
          usePolling: true
        }
    },
});

