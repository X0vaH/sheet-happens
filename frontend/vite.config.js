import react from '@vitejs/plugin-react';
import { defineConfig } from 'vite';

// Compose sets CHOKIDAR_USEPOLLING on the frontend service. When that is
// present, the browser reaches Vite only through Nginx on port 80 — HMR
// must not advertise the container bind (localhost:5173), or the client
// falls through Docker Desktop / WSL port publish instead of the proxy.
const behindInfraProxy = process.env.CHOKIDAR_USEPOLLING === 'true';

// https://vite.dev/config/
export default defineConfig({
    plugins: [react()],
    server: {
        // Le backend n'autorise (CORS + Sanctum stateful domains) que
        // localhost:5174 : si ce port est déjà pris, Vite ne doit pas
        // glisser silencieusement vers un autre port, sinon toutes les
        // requêtes authentifiées échouent (CORS) de façon peu explicite.
        // Sous Docker, entrypoint force --port 5173; HMR clientPort reste 80.
        port: 5174,
        strictPort: true,
        // Docker bind mounts on Windows may not forward file change events.
        watch: {
            usePolling: true,
        },
        ...(behindInfraProxy
            ? {
                  hmr: {
                      protocol: 'ws',
                      host: '127.0.0.1',
                      clientPort: 80,
                  },
              }
            : {}),
    },
});
