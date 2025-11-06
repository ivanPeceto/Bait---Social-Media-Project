import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 4200,
    hmr: {
      host: '172.24.40.52',
      protocol: 'ws',
      port: 4200
    }
  }
})
