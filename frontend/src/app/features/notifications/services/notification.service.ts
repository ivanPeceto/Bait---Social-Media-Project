import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

// Interfaz que representa UNA notificación según tu API
export interface Notification {
  id: string; // Es un UUID
  type: string;
  data: {
    follower_id?: number; // Hacemos opcionales por si hay otros tipos de notif
    follower_name?: string;
    message: string; // El mensaje parece ser el campo común
    // ... aquí podrían ir otros campos para diferentes tipos de notificaciones
  };
  read_at: string | null;
  created_at: string;
}

// Interfaz para la RESPUESTA COMPLETA de la API (envuelta en 'data')
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
      map(response => response.data) // Extraemos el array de notificaciones
    );
  }

  /**
   * Marca una notificación específica como leída.
   * Corresponde a: PUT /api/notifications/{notification_id}
   * (Implementación básica, el backend debe manejar la lógica)
   */
  markAsRead(notificationId: string): Observable<any> {
    // Asumimos que la API espera un cuerpo vacío o un campo específico
    return this.http.put(`${this.apiUrl}/${notificationId}`, { is_read: true }); 
  }
}