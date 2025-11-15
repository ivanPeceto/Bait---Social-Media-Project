import { Component, inject, OnInit, ChangeDetectorRef, HostListener, ElementRef, NgZone } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink } from '@angular/router';
import { Observable, map } from 'rxjs';
import { AuthService } from '../../core/services/auth.service';
import { EchoService } from '../../core/services/echo.service';
import { NotificationService, Notification } from '../../core/services/notification.service';
import { NotificationListenerService } from '../../core/services/notification.listener.service';
import { ChatComponent } from '../../features/chat/chat.component';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink, ChatComponent],
  templateUrl: './main.component.html',
})
export class MainComponent implements OnInit {
  private authService = inject(AuthService);
  private echoService = inject(EchoService);
  private notificationService = inject(NotificationService);
  private notificationListener = inject(NotificationListenerService);
  private cdr = inject(ChangeDetectorRef);
  private zone = inject(NgZone);
  private elementRef = inject(ElementRef);

  public currentUser: any = null;
  public notifications$: Observable<Notification[]> = this.notificationService.notifications$;
  public unreadNotificationsCount$!: Observable<number>;
  public showNotificationsPanel = false;
  public isLoadingNotifications = false;
  public isPrivileged = false;

  ngOnInit(): void {
    this.notificationService.loadNotifications().subscribe({
      next: () => this.cdr.detectChanges(),
    });

    this.authService.currentUserChanges$.subscribe((user) => {
      if (!user) return;
      this.currentUser = user;
      this.isPrivileged = ['admin', 'moderator'].includes(user.role);
      const token = this.authService.getAccessToken();
      if (!token) return;
      this.echoService.initEcho(token, () => {
        console.log('✅ Echo inicializado');
        this.notificationListener.registerUserNotifications(this.currentUser.id);
      });
    });

    this.unreadNotificationsCount$ = this.notifications$.pipe(
      map((n) => n.filter((x) => !x.read_at).length)
    );
  }

  toggleNotificationsPanel(): void {
    this.showNotificationsPanel = !this.showNotificationsPanel;

    if (this.showNotificationsPanel) {
      this.isLoadingNotifications = true;

      // 🔹 Forzar recarga real desde backend (no solo refresh local)
      this.notificationService.loadNotifications().subscribe({
        next: () => {
          // 🔸 Ejecutar dentro del ciclo de Angular
          this.zone.run(() => {
            this.isLoadingNotifications = false;
            this.cdr.detectChanges();
          });
        },
        error: () => {
          this.zone.run(() => {
            this.isLoadingNotifications = false;
            this.cdr.detectChanges();
          });
        },
      });
    } else {
      this.cdr.detectChanges();
    }
  }

  @HostListener('document:click', ['$event'])
  onClickOutside(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (
      !target.closest('.notifications-panel') &&
      !target.closest('[data-role="notifications-button"]') &&
      this.showNotificationsPanel
    ) {
      this.showNotificationsPanel = false;
      this.cdr.detectChanges();
    }
  }

  markNotificationAsRead(notification: Notification, event: MouseEvent): void {
    event.stopPropagation();
    if (!notification.read_at) {
      this.notificationService.markAsRead(notification.id);
    }
  }

  trackById(index: number, item: Notification): string {
    return item.id;
  }

  logout(): void {
    this.echoService.echo?.disconnect();
    this.notificationService.clear();
    this.authService.logout();
  }
}
