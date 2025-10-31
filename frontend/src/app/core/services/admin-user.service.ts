// en src/app/features/admin/services/admin-user.service.ts

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

export interface User {
  id: number;
  username: string;
  name: string;
  role: string;
  state: string;
  email?: string; 
}

interface UsersResponse {
  data: User[];
}

@Injectable({
  providedIn: 'root'
})
export class AdminUserService {
  private http = inject(HttpClient);
  private privilegedApiUrl = `${environment.apiUrl}/privileged/users`;

  getUsers(): Observable<User[]> {
    return this.http.get<UsersResponse>(this.privilegedApiUrl).pipe(
      map(response => response.data)
    );
  }

  /**
   * Actualiza los datos de un usuario. Ahora es mucho más flexible.
   * Corresponde a: PUT /privileged/users/{user}/update
   */
  updateUser(userId: number, data: { name?: string; username?: string; email?: string; role_id?: number; state_id?: number }): Observable<User> {
    return this.http.put<User>(`${this.privilegedApiUrl}/${userId}/update`, data);
  }

  /**
   * Cambia la contraseña de un usuario, ahora enviando la confirmación.
   * Corresponde a: PUT /privileged/users/{user}/password
   */
  changeUserPassword(userId: number, password: string, passwordConfirmation: string): Observable<any> {
    return this.http.put(`${this.privilegedApiUrl}/${userId}/password`, {
      new_password: password,
      new_password_confirmation: passwordConfirmation
    });
  }

  deleteUserAvatar(userId: number): Observable<any> {
    return this.http.delete(`${this.privilegedApiUrl}/${userId}/avatar`);
  }

  deleteUserBanner(userId: number): Observable<any> {
    return this.http.delete(`${this.privilegedApiUrl}/${userId}/banner`);
  }
}