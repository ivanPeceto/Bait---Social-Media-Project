const appUrlBase = import.meta.env.VITE_APP_URL_BASE || 'http://localhost';
const reverbAppKey: string = import.meta.env.VITE_REVERB_APP_KEY || 'reverb_app_key';

export const environment = {
  production: false,
  apiUrl: `${appUrlBase}/api`, 
  baseUrl: appUrlBase,
  wsUrl: appUrlBase,
  reverbKey: reverbAppKey,
};