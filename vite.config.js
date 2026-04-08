import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';
import tailwindcss from "@tailwindcss/vite";

async function loadInstruckt() {
    try {
        const mod = await import('instruckt/vite');
        return mod.default({ server: false, endpoint: '/instruckt', adapters: ['react', 'blade'], mcp: true });
    } catch {
        return null;
    }
}

export default defineConfig(async () => ({
    plugins: [
        await loadInstruckt(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.jsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ].filter(Boolean),
    esbuild: {
        jsx: 'automatic',
    },
    server: {
        watch: {
            ignored: ['**/.junie/**'],
        },
    },
}));
