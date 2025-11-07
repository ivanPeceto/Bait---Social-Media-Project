/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_APP_URL_BASE: string;
  readonly VITE_HOST_IP: string;
  readonly VITE_REVERB_APP_KEY: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}