import { Injectable, inject } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { map, tap } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

export interface Notification {
  id: string;
  type: string;
  data: {
    follower_id?: number;
    follower_name?: string;
    message: string;
  };
  read_at: string | null;
  created_at: string;
}

interface NotificationsResponse {
  data: Notification[];
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}/notifications`;

  // Store reactivo
  private notificationsSubject = new BehaviorSubject<Notification[]>([]);
  public notifications$ = this.notificationsSubject.asObservable();

  /** Carga las notificaciones desde el backend y actualiza el store */
  loadNotifications(): Observable<Notification[]> {
    return this.http.get<NotificationsResponse>(this.apiUrl)
      .pipe(
        map(res => res.data),
        tap(notifs => this.notificationsSubject.next(notifs))
      );
  }

  /** Marca una notificación como leída en el backend y en el store local */
  markAsRead(notificationId: string): void {
    this.http.put(`${this.apiUrl}/${notificationId}`, { is_read: true })
      .subscribe(() => {
        const updated = this.notificationsSubject.value.map(n =>
          n.id === notificationId ? { ...n, read_at: new Date().toISOString() } : n
        );
        this.notificationsSubject.next(updated);
      });
  }

  /** Agrega una notificación nueva al store (por ejemplo, desde un WebSocket) */
  addNotification(notification: Notification): void {
    const current = this.notificationsSubject.value;
    const updated = [notification, ...current];
    this.notificationsSubject.next(updated);
  }

  refresh(): void {
    const current = this.notificationsSubject.value;
    this.notificationsSubject.next([...current]);
  }

  /** Borra todas las notificaciones del store */
  clear(): void {
    this.notificationsSubject.next([]);
  }

  /** Número de notificaciones no leídas */
  get unreadCount(): number {
    return this.notificationsSubject.value.filter(n => !n.read_at).length;
  }
}
