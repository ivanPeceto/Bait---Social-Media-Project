import { Injectable, inject } from '@angular/core';
import { NotificationService } from './notification.service';
import { EchoService } from './echo.service';

@Injectable({
    providedIn: 'root'
})
export class NotificationListenerService {
    private notificationService = inject(NotificationService);
    private echoService = inject(EchoService);

    constructor() {
        const userId = localStorage.getItem('user_id');
        if (!userId || !this.echoService.echo) return;

        const notificationTypes = [
            'NewFollowNotification',
            'NewReactionNotification',
            'NewRepostNotification'
        ];

        notificationTypes.forEach(type => {
            this.echoService.listenPrivate(
                `App.Models.User.${userId}`,
                `.App\\Notifications\\${type}`,
                (data: any) => {
                    console.log('🔔 Notificación recibida:', data);
                    this.notificationService.addNotification(data);
                }
            );
        });
    }
}

