import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Post } from '../models/post.model';
import { CreateReactionPayload, CreateRepostPayload } from '../models/api-payloads.model';
import { UserReactionStatus } from '../models/user-reaction-status.model';

@Injectable({
  providedIn: 'root',
})
export class InteractionService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl;

  /**
   * Envía una reacción (like) o la quita a un post.
   * Llama a POST /api/post-reactions.
   * El backend maneja la lógica de 'toggle' y devuelve el Post actualizado.
   *
   * @param payload Objeto con { post_id, reaction_type_id }
   * @returns Observable que emite el objeto Post actualizado.
   */
  toggleReaction(payload: CreateReactionPayload): Observable<Post> {
    return this.http.post<Post>(`${this.apiUrl}/post-reactions`, payload);
  }

  /**
   * Verifica si el usuario actual ha reaccionado a un post específico.
   * Llama a GET /api/posts/{postId}/user-reaction.
   *
   * @param postId El ID del post a verificar.
   * @returns Observable que emite un objeto UserReactionStatus.
   */
  checkUserReaction(postId: number): Observable<UserReactionStatus> {
    return this.http.get<UserReactionStatus>(`${this.apiUrl}/posts/${postId}/user-reaction`);
  }

  /**
   * Crea o elimina un repost para un post específico.
   * Llama a POST /api/reposts.
   * El backend maneja la lógica de 'toggle' y devuelve el Post actualizado.
   *
   * @param payload Objeto con { post_id }
   * @returns Observable que emite el objeto Post actualizado.
   */
  toggleRepost(payload: CreateRepostPayload): Observable<Post> {
    return this.http.post<Post>(`${this.apiUrl}/reposts`, payload);
  }
}