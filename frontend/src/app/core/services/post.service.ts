import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Post, Repost } from '../models/post.model';
import { PaginatedResponse } from '../models/api-payloads.model';
import { ReactionSummary } from '../models/reaction-type.model';

@Injectable({
  providedIn: 'root'
})

export class PostService {
  private http = inject(HttpClient);
  private API_URL = `${environment.apiUrl}/posts`;
  private PRIV_API_URL = `${environment.apiUrl}/privileged/multimedia`;
  private FEED_API_URL = `${environment.apiUrl}/feed`;

  getPosts(): Observable<any> {
    return this.http.get<Post[]>(this.API_URL);
  }

  /**
   * Obtiene el feed principal paginado del usuario (posts y reposts).
   * @param page El número de página a solicitar.
   * @param perPage La cantidad de items por página.
   */
  getFeed(page: number = 1, perPage: number = 15): Observable<PaginatedResponse<Post | Repost>> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('per_page', perPage.toString());

    return this.http.get<PaginatedResponse<Post | Repost>>(this.FEED_API_URL, { params });
  }

  getPostById(postId: number): Observable<Post> {
    return this.http.get<Post>(`${this.API_URL}/${postId}`);
  }

  createPost(content: string): Observable<Post> {
    return this.http.post<Post>(this.API_URL, { content_posts: content });
  }

  deletePost(postId: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${postId}`);
  }

  deletePostPrivileged(postId: number): Observable<any> {
    return this.http.delete(`${this.PRIV_API_URL}/post/${postId}`);
  }

  updatePost(postId: number, content: string): Observable<Post> {
    return this.http.put<Post>(`${this.API_URL}/${postId}`, { content_posts: content });
  }

  /**
   * Obtiene el resumen de reacciones (conteo por tipo) para un post específico.
   * Llama a: GET /api/posts/{postId}/reaction-summary
   * @param postId El ID del post.
   */
  getReactionSummary(postId: number): Observable<ReactionSummary[]> {
    return this.http.get<ReactionSummary[]>(`${this.API_URL}/${postId}/reaction-summary`);
  }
}