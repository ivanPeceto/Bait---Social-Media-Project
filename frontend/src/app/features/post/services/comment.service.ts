import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CommentService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl;


  getCommentsForPost(postId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/comments`, { params: { post_id: postId.toString() } });
  }


  createComment(postId: number, content: string): Observable<Comment> {
    const endpointUrl = `${this.apiUrl}/comments`;
    const body = { 
      content_comments: content,
      post_id: postId 
    };
    return this.http.post<Comment>(endpointUrl, body);
  }



  deleteComment(postId: number, commentId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/posts/${postId}/comments/${commentId}`);
  }
}