import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';
import { User } from '../../../core/models/user.model'; 

@Injectable({
  providedIn: 'root'
})
export class ProfileService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl; // Fusión de la definición de apiUrl

  getOwnProfile(): Observable<User> {
    const params = new HttpParams().set('cacheBuster', new Date().getTime().toString());
    return this.http.get<User>(`${this.apiUrl}/profile/show`, { params });
  }

  getUserProfile(id: string): Observable<User> {
    const params = new HttpParams().set('cacheBuster', new Date().getTime().toString());
    return this.http.get<User>(`${this.apiUrl}/users/${id}`, { params });
  }

  getUserPosts(id: string): Observable<any> { 
    return this.http.get<any>(`${this.apiUrl}/users/${id}/posts`);
  }

  uploadBanner(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('banner', file, file.name); 
    return this.http.post<any>(`${this.apiUrl}/banners/upload`, formData);
  }

  uploadAvatar(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('avatar', file, file.name); 
    return this.http.post<any>(`${this.apiUrl}/avatars/upload`, formData);
  }

  getPublicProfile(username: string): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/users/${username}`);
  }
}