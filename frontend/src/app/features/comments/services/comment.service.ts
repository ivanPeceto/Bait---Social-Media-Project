// Archivo: src/app/features/comments/services/comment.service.ts
import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';
import { Comment } from '../../../core/models/comment.model';

@Injectable({
  providedIn: 'root',
})
export class CommentService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl;

  /**
   * Obtiene los comentarios para un post específico.
   * Corresponde a: GET /api/comments/post/{postId}
   */
  getCommentsForPost(postId: number): Observable<Comment[]> {
    return this.http.get<Comment[]>(`${this.apiUrl}/comments/post/${postId}`);
  }

  /**
   * Crea un nuevo comentario para un post.
   * Corresponde a: POST /api/comments
   */
  createComment(postId: number, content: string): Observable<Comment> {
    const body = {
      post_id: postId,
      content_comments: content,
    };
    return this.http.post<Comment>(`${this.apiUrl}/comments`, body);
  }

  /**
   * Actualiza un comentario existente.
   * Corresponde a: PUT /api/comments/{commentId}
   */
  updateComment(commentId: number, content: string): Observable<Comment> {
    const body = {
      content_comments: content,
    };
    return this.http.put<Comment>(`${this.apiUrl}/comments/${commentId}`, body);
  }

  /**
   * Elimina un comentario.
   * Corresponde a: DELETE /api/comments/{commentId}
   */
  deleteComment(commentId: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/comments/${commentId}`);
  }

  deleteCommentAsAdmin(commentId: number): Observable<any> {
    return this.http.delete<any>(
      `${environment.apiUrl}/privileged/multimedia/comment/${commentId}`
    );
  }
}
