import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';

export interface Post {
  id: number;
  content_posts: string;
  user: any; 
  created_at: string;
}

const API_URL = `${environment.apiUrl}/posts`;

@Injectable({
  providedIn: 'root'
})

export class PostService {
  private http = inject(HttpClient);

  getPosts(): Observable<Post[]> {
    return this.http.get<Post[]>(API_URL);
  }

  createPost(content: string): Observable<Post> {
    return this.http.post<Post>(API_URL, { content_posts: content });
  }
}