import { Injectable, Inject, PLATFORM_ID } from '@angular/core';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { isPlatformBrowser } from '@angular/common';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class EchoService {
  public echo: Echo<'pusher'> | null = null;

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    console.log('EchoService creado, pero no inicializado');
  }

  /**
   * Inicializa Echo con el token JWT
   */
  public initEcho(token: string, onReady?: () => void) {
    if (!isPlatformBrowser(this.platformId)) return;
    if (!token) return console.error('Token JWT faltante para inicializar Echo');

    (window as any).Pusher = Pusher;

    const reverbAppKey = environment.reverbKey;
    const wsHost = new URL(environment.wsUrl).hostname;
    const wsPort = new URL(environment.wsUrl).port || 80;
    const forceTLS = new URL(environment.wsUrl).protocol === 'https:';
    const authEndpoint = `${environment.apiUrl}/broadcasting/auth`;
    const tokenJWT = localStorage.getItem('jwt');

    this.echo = new Echo({
      broadcaster: 'pusher',
      key: reverbAppKey,
      cluster: 'local',
      wsHost: wsHost,
      wsPort: Number(wsPort),
      forceTLS: forceTLS,
      disableStats: true,
      enabledTransports: ['ws'],
      wsPath: '/ws', 
      authEndpoint: authEndpoint,
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json'
        }
      }
    });

    (this.echo as any).connector.pusher.connection.bind('connected', () => {
      console.log('Echo conectado ✅');
      if (onReady) onReady();
    });

    (this.echo as any).connector.pusher.connection.bind('error', (err: any) => {
      console.error('WS Error:', err);
    });
  }

  public onConnected(callback: () => void) {
    if (!this.echo) return;

    const pusher = (this.echo as any).connector.pusher;
    if (pusher.connection.state === 'connected') {
      callback();
    } else {
      pusher.connection.bind('connected', callback);
    }
  }

  /**
   * Escucha un canal privado y parsea automáticamente el payload
   */
  public listenPrivate(channel: string, event: string, callback: (data: any) => void) {
    if (!this.echo) return;

    const privateChannel = this.echo.private(channel);

    privateChannel.stopListening(event);

    privateChannel.listen(event, (payload: any) => {
      try {
        let parsedData = payload;
        if (payload?.data && typeof payload.data === 'string') {
          parsedData = JSON.parse(payload.data);
        }
        console.log(`📡 Evento recibido en canal ${channel}:`, parsedData);
        callback(parsedData);
      } catch (err) {
        console.error('⚠️ Error parseando payload de Echo:', err, payload);
      }
    });
  }

}
