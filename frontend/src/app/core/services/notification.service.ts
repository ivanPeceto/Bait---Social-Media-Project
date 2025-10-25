import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
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

  /**
   * Obtiene las notificaciones del usuario autenticado.
   * Corresponde a: GET /api/notifications
   */

  getNotifications(): Observable<Notification[]> {

    return this.http.get<NotificationsResponse>(this.apiUrl).pipe(
      map(response => response.data) 
    );
  }

  /**
   * Marca una notificación específica como leída.
   * Corresponde a: PUT /api/notifications/{notification_id}
   * (Implementación básica, el backend debe manejar la lógica)
   */
  markAsRead(notificationId: string): Observable<any> {
    return this.http.put(`${this.apiUrl}/${notificationId}`, { is_read: true }); 
  }
}