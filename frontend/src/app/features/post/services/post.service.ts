import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';

export interface Post {
  id: number;
  content_posts: string;
  user: User; 
  user_id: number; 
  created_at: string;
  reactions_count: number;
  is_liked_by_user: boolean;
  comments: PostComment[];
}
export interface PostComment {
  id: number;
  content_comments: string;
  user: User;
  user_id: User; 
  created_at: string;
  post_id: number;
}

export interface User {
  id: number;
  name: string;
  username: string;
}

@Injectable({
  providedIn: 'root'
})

export class PostService {
  private http = inject(HttpClient);
  private API_URL = `${environment.apiUrl}/posts`;

  getPosts(): Observable<Post[]> {
    return this.http.get<Post[]>(this.API_URL);
  }

  createPost(content: string): Observable<Post> {
    return this.http.post<Post>(this.API_URL, { content_posts: content });
  }

  deletePost(postId: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${postId}`);
  }
  likePost(postId: number): Observable<any> {
    const reactionTypeId = 1;

    return this.http.post(`${this.API_URL}/${postId}/reactions`, {
      reaction_type_id: reactionTypeId
    });
  }

  unlikePost(postId: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${postId}/reactions`);
  }
}