import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';
import { User } from '../../../core/models/user.model';


export interface MultimediaContent {
  id: number;
  url_content: string;
}
export interface Post {
  id: number;
  content_posts: string;
  user: User;
  user_id: number; 
  created_at: string;
  multimedia_contents?: MultimediaContent[];
}

@Injectable({
  providedIn: 'root'
})

export class PostService {
  private http = inject(HttpClient);
  private API_URL = `${environment.apiUrl}/posts`;


  getPosts(): Observable<any> {
    return this.http.get<Post[]>(this.API_URL);
  }

  createPost(content: string): Observable<Post> {
    return this.http.post<Post>(this.API_URL, { content_posts: content });
  }

  deletePost(postId: number): Observable<any> {
    return this.http.delete(`${this.API_URL}/${postId}`);
  }

}