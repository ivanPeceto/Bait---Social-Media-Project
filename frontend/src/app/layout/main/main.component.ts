import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { User } from '../../core/models/user.model';
import { NotificationService, Notification } from '../../core/services/notification.service';
import { Observable, tap } from 'rxjs';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  templateUrl: './main.component.html',
})
export class MainComponent implements OnInit {
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  public currentUser: User | any | null = null;
  public notifications$: Observable<Notification[]> | null = null;
  public showNotificationsPanel = false;
  public unreadNotificationsCount = 0;

  ngOnInit(): void {
   
    this.authService.currentUserChanges$.subscribe(user => {
      this.currentUser = user;
    });
    this.loadNotifications();
  }

  loadNotifications(): void {
    this.notifications$ = this.notificationService.getNotifications().pipe(
      tap(notifications => {
        if (Array.isArray(notifications)) {
            this.unreadNotificationsCount = notifications.filter(n => !n.read_at).length;
        } else {
            this.unreadNotificationsCount = 0;
        }
      })
    );
  }

  toggleNotificationsPanel(): void {
    this.showNotificationsPanel = !this.showNotificationsPanel;
  }

  markNotificationAsRead(notification: Notification, event: MouseEvent): void {
    event.stopPropagation();
    if (!notification.read_at) {
      this.notificationService.markAsRead(notification.id).subscribe({
        next: () => {
          notification.read_at = new Date().toISOString();
          if (this.unreadNotificationsCount > 0) {
            this.unreadNotificationsCount--;
          }
        },
        error: (err) => console.error("Error al marcar notificación como leída:", err)
      });
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