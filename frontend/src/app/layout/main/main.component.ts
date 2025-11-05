import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { NotificationService, Notification } from '../../core/services/notification.service';
import { NotificationListenerService } from '../../core/services/notification.listener.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  templateUrl: './main.component.html',
})
export class MainComponent implements OnInit {
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private notificationListener = inject(NotificationListenerService);

  public currentUser: any = null;
  public notifications$: Observable<Notification[]> = this.notificationService.notifications$;
  public showNotificationsPanel = false;
  public unreadNotificationsCount = 0;

  ngOnInit(): void {
    // Obtener usuario actual
    this.authService.currentUserChanges$.subscribe((user) => {
      this.currentUser = user;
      // El NotificationListenerService ya se encargará de escuchar las notificaciones
    });

    // Actualiza el contador automáticamente cuando cambia la lista de notificaciones
    this.notifications$.subscribe((notifications) => {
      this.unreadNotificationsCount = notifications.filter((n) => !n.read_at).length;
    });
  }

  toggleNotificationsPanel(): void {
    this.showNotificationsPanel = !this.showNotificationsPanel;
  }

  markNotificationAsRead(notification: Notification, event: MouseEvent): void {
    event.stopPropagation();
    if (!notification.read_at) {
      this.notificationService.markAsRead(notification.id);
    }
  }

  isPrivilegedUser(): boolean {
    if (!this.currentUser || !this.currentUser.role) {
      return false;
    }
    const roleName = this.currentUser.role;
    return roleName === 'admin' || roleName === 'moderator';
  }

  logout(): void {
    this.authService.logout();
  }
}
