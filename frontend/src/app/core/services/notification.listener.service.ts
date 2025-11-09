import { Injectable, inject, NgZone } from '@angular/core';
import { NotificationService } from './notification.service';
import { EchoService } from './echo.service';

@Injectable({
    providedIn: 'root'
})
export class NotificationListenerService {
    private notificationService = inject(NotificationService);
    private echoService = inject(EchoService);
    private ngZone = inject(NgZone);
    private initializedUsers = new Set<string>();


    public registerUserNotifications(userId: string) {
        if (this.initializedUsers.has(userId)) return;

        const tryRegister = () => {
            if (!this.echoService.echo) {
                console.warn('⏳ Esperando que Echo esté listo...');
                setTimeout(tryRegister, 500);
                return;
            }

            console.log(`🎧 Registrando listener para users.${userId}`);

            this.echoService.listenPrivate(
                `users.${userId}`,
                '.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated',
                (eventData: any) => {
                    console.log('📩 Evento recibido:', eventData);

                    const parsed = typeof eventData.data === 'string'
                        ? JSON.parse(eventData.data)
                        : eventData.data;

                    this.ngZone.run(() => {
                        this.notificationService.addNotification(parsed);
                    });
                }
            );

            this.initializedUsers.add(userId);
        };

        tryRegister();
    }

}
