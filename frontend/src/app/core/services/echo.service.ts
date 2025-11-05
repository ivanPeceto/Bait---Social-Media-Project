import { Injectable, Inject, PLATFORM_ID } from '@angular/core';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { isPlatformBrowser } from '@angular/common';

@Injectable({
  providedIn: 'root'
})
export class EchoService {
  public echo: Echo<'pusher'> | null = null;

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    if (isPlatformBrowser(this.platformId)) {
      (window as any).Pusher = Pusher;

      const token = localStorage.getItem('jwt'); // <-- tu JWT almacenado

      this.echo = new Echo({
        broadcaster: 'pusher',
        key: 'reverb_app_key',
        cluster: 'local',
        wsHost: '172.24.40.52',
        wsPort: 80,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws'],
        wsPath: '/ws', 
        authEndpoint: 'http://172.24.40.52/api/broadcasting/auth',
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
