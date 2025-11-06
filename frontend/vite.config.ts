import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return{
    define: {
      'process.env': {
        VITE_APP_URL_BASE: JSON.stringify(env.VITE_APP_URL_BASE || `http://${env.IP_ADDRESS || 'localhost'}`),
        REVERB_APP_KEY: JSON.stringify(env.REVERB_APP_KEY || `reverb_app_key`),
        VITE_HOST_IP: JSON.stringify(env.VITE_HOST_IP || 'localhost')
      }
    },
    resolve: {
      mainFields: ['module'],
    },
    server: {
      host: '0.0.0.0',
      port: 4200,
      hmr: {
        host: env.VITE_HOST_IP || 'localhost',
        protocol: 'ws',
        port: 4200
      }
    }
  }
})
