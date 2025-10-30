import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { FollowPayload, FollowUserInfo } from '../models/api-payloads.model';

@Injectable({
  providedIn: 'root',
})
export class FollowService {
  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}`;

  /**
   * Sigue a un usuario.
   * Llama a POST /api/follows
   * @param payload Objeto con { following_id }
   * @returns Observable (la respuesta exacta puede variar, aquí usamos 'any')
   */
  followUser(payload: FollowPayload): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/follows`, payload);
  }

  /**
   * Deja de seguir a un usuario.
   * Llama a DELETE /api/follows
   * @param payload Objeto con { following_id }
   * @returns Observable (usualmente vacío o con mensaje de éxito)
   */
  unfollowUser(payload: FollowPayload): Observable<any> {
    const options = {
      headers: new HttpHeaders({
        'Content-Type': 'application/json',
      }),
      body: payload,
    };
    return this.http.delete<any>(`${this.apiUrl}/follows`, options);
  }

  /**
   * Obtiene la lista de seguidores de un usuario.
   * Llama a GET /api/users/{userIdOrUsername}/followers
   * @param userIdOrUsername ID o username del usuario
   * @returns Observable que emite un array de FollowUserInfo
   */
  getFollowers(userIdOrUsername: number | string): Observable<FollowUserInfo[]> {
    return this.http.get<FollowUserInfo[]>(`${this.apiUrl}/users/${userIdOrUsername}/followers`);
  }

  /**
   * Obtiene la lista de usuarios a los que sigue un usuario.
   * Llama a GET /api/users/{userIdOrUsername}/following
   * @param userIdOrUsername ID o username del usuario
   * @returns Observable que emite un array de FollowUserInfo
   */
  getFollowing(userIdOrUsername: number | string): Observable<FollowUserInfo[]> {
    return this.http.get<FollowUserInfo[]>(`${this.apiUrl}/users/${userIdOrUsername}/following`);
  }

}