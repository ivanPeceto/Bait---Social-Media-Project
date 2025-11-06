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
    if (isPlatformBrowser(this.platformId)) {
      (window as any).Pusher = Pusher;

      const reverbAppKey = environment.reverbKey;
      const wsHost = new URL(environment.wsUrl).hostname;
      const wsPort = new URL(environment.wsUrl).port || 80;
      const forceTLS = new URL(environment.wsUrl).protocol === 'https:';
      const authEndpoint = `${environment.apiUrl}/broadcasting/auth`;
      const token = localStorage.getItem('jwt');

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
    }
  }

  /**
   * Escucha un canal privado de usuario, ejemplo: App.Models.User.1
   */
  listenPrivate(channel: string, event: string, callback: (data: any) => void) {
    if (!this.echo) return;
    this.echo.private(channel).listen(event, (data: any) => callback(data));
  }
}
