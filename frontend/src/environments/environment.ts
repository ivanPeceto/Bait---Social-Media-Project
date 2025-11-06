const appUrlBase = (process.env as any).VITE_APP_URL_BASE || 'http://localhost';
const reverbAppKey: string = (process.env as any).REVERB_APP_KEY || 'reverb_app_key';

export const environment = {
  production: false,
  apiUrl: `${appUrlBase}/api`, 
  baseUrl: appUrlBase,
  wsUrl: appUrlBase,
  reverbKey: reverbAppKey,
};