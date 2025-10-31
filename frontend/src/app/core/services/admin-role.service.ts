import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';

export interface UserRole {
  id: number;
  name: string;
}

// Interfaz para la respuesta de una colección
interface RolesResponse {
  data: UserRole[];
}

// Interfaz para la respuesta de un único item
interface RoleResponse {
  data: UserRole;
}

const API_URL = `${environment.apiUrl}/roles`;

@Injectable({
  providedIn: 'root'
})
export class AdminRoleService {
  private http = inject(HttpClient);

  getRoles(): Observable<UserRole[]> {
    return this.http.get<RolesResponse>(API_URL).pipe(
      map(response => response.data)
    );
  }

  createRole(name: string): Observable<UserRole> {
    // --- MÉTODO CORREGIDO ---
    return this.http.post<RoleResponse>(API_URL, { name: name }).pipe(
      map(response => response.data) 
    );
  }

  updateRole(id: number, name: string): Observable<UserRole> {
    // --- MÉTODO CORREGIDO ---
    return this.http.put<RoleResponse>(`${API_URL}/${id}`, { name: name }).pipe(
      map(response => response.data) 
    );
  }

  deleteRole(id: number): Observable<any> {
    return this.http.delete(`${API_URL}/${id}`);
  }
}