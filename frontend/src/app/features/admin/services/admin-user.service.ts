import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';

// Definimos una 'interfaz' para el modelo de Usuario. Esto ayuda a que el código sea más robusto.
// Puedes crear una carpeta 'core/models' y poner este archivo ahí si quieres.
export interface User {
  id: number;
  username: string;
  email: string;
  role: { id: number; name_user_roles: string; };
  state: { id: number; name_user_states: string; };
}

// El servicio para todas las acciones de Admin sobre Usuarios
@Injectable({
  providedIn: 'root'
})
export class AdminUserService {
  private http = inject(HttpClient);
  
  // URL base para los endpoints de gestión de usuarios
  private privilegedApiUrl = `${environment.apiUrl}/privileged/users`;
  private publicApiUrl = `${environment.apiUrl}/users`; // Asumiendo que esta es la ruta para obtener todos los usuarios

  // --- MÉTODOS DE GESTIÓN DE USUARIOS ---

  /**
   * Obtiene la lista completa de usuarios.
   * NOTA: Este endpoint debe existir en tu backend (ej. GET /api/users)
   */
  getUsers(): Observable<User[]> {
    // Apuntamos a la ruta pública de usuarios para obtener la lista
    return this.http.get<User[]>(this.publicApiUrl);
  }

  /**
   * Envía una petición para suspender a un usuario por su ID.
   */
  suspendUser(userId: number): Observable<any> {
    return this.http.post(`${this.privilegedApiUrl}/${userId}/suspend`, {});
  }

  /**
   * Envía una petición para activar a un usuario por su ID.
   */
  activateUser(userId: number): Observable<any> {
    return this.http.post(`${this.privilegedApiUrl}/${userId}/activate`, {});
  }
}