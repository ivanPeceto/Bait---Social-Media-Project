import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');

  return{
    resolve: {
      mainFields: ['module'],
    },
    server: {
      host: 'https://temporary-complement-harris-utility.trycloudflare.com/',
      port: 4200,
      hmr: {
        host: env.VITE_HOST_IP || 'localhost',
        protocol: 'ws',
        port: 4200
      }
    }
  }
})
