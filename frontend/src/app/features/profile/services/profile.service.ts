import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../../environments/environment';
import { User } from '../../../core/models/user.model'; 

@Injectable({
  providedIn: 'root'
})
export class ProfileService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl; 
  getOwnProfile(): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/profile/show`);
  }
}