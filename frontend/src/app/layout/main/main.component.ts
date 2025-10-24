// en src/app/layout/main/main.component.ts (CÓDIGO COMPLETO Y CORREGIDO)

import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common'; // Import CommonModule for async pipe, ngIf, ngFor
import { RouterOutlet, RouterLink } from '@angular/router';
import { AuthService } from '../../features/auth/services/auth.service';
import { User } from '../../core/models/user.model';
// --- 👇 CORRECTAS IMPORTACIONES PARA NOTIFICACIONES ---
import { NotificationService, Notification } from '../../features/notifications/services/notification.service';
import { Observable, tap } from 'rxjs'; // Asegúrate de importar Observable y tap

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink], // CommonModule es necesario
  templateUrl: './main.component.html',
})
export class MainComponent implements OnInit {
  // --- Inyecciones de Servicios ---
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService); // <-- Inyectar NotificationService

  // --- Propiedades del Componente ---
  public currentUser: User | any | null = null;

  // --- 👇 PROPIEDADES NECESARIAS PARA NOTIFICACIONES ---
  public notifications$: Observable<Notification[]> | null = null;
  public showNotificationsPanel = false;
  public unreadNotificationsCount = 0;

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadNotifications(); // <-- Llamar a la carga de notificaciones
  }

  // --- 👇 MÉTODOS NECESARIOS PARA NOTIFICACIONES ---

  loadNotifications(): void {
    this.notifications$ = this.notificationService.getNotifications().pipe(
      tap(notifications => {
        // Asegúrate que notifications sea un array antes de filtrar
        if (Array.isArray(notifications)) {
            this.unreadNotificationsCount = notifications.filter(n => !n.read_at).length;
        } else {
            this.unreadNotificationsCount = 0; // O maneja el caso como prefieras
        }
      })
    );
  }

  toggleNotificationsPanel(): void {
    this.showNotificationsPanel = !this.showNotificationsPanel;
  }

  markNotificationAsRead(notification: Notification, event: MouseEvent): void {
    event.stopPropagation(); // Evita que el panel se cierre al hacer clic
    if (!notification.read_at) {
      this.notificationService.markAsRead(notification.id).subscribe({
        next: () => {
          // Actualiza la notificación localmente para feedback instantáneo
          notification.read_at = new Date().toISOString();
          if (this.unreadNotificationsCount > 0) {
            this.unreadNotificationsCount--; // Decrementa el contador solo si es mayor a 0
          }
        },
        error: (err) => console.error("Error al marcar notificación como leída:", err)
      });
    }
    // Opcional: Navegar a la fuente de la notificación
    // console.log("Clic en notificación:", notification);
  }

  // --- Métodos existentes ---
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