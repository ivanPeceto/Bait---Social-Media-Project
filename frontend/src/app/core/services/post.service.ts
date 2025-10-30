import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Post } from '../models/post.model';

@Injectable({
  providedIn: 'root'
})

export class PostService {
  private http = inject(HttpClient);
  private API_URL = `${environment.apiUrl}/posts`;


  getPosts(): Observable<any> {
    return this.http.get<Post[]>(this.API_URL);
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

  updatePost(postId: number, content: string): Observable<Post> {
    return this.http.put<Post>(`${this.API_URL}/${postId}`, { content_posts: content });
  }

}