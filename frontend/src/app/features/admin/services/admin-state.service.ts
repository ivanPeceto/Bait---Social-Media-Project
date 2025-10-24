import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

export interface UserState {
  id: number;
  name: string;
}

// Interfaz para la respuesta de una colección
interface StatesResponse {
  data: UserState[];
}

// Interfaz para la respuesta de un único item
interface StateResponse {
  data: UserState;
}

const API_URL = `${environment.apiUrl}/states`;

@Injectable({
  providedIn: 'root'
})
export class AdminStateService {
  private http = inject(HttpClient);

  getStates(): Observable<UserState[]> {
    return this.http.get<StatesResponse>(API_URL).pipe(
      map(response => response.data) // Desenvuelve la colección
    );
  }

  createState(name: string): Observable<UserState> {
    // --- MÉTODO CORREGIDO ---
    return this.http.post<StateResponse>(API_URL, { name: name }).pipe(
      map(response => response.data) // Desenvuelve el item único
    );
  }

  updateState(id: number, name: string): Observable<UserState> {
    // --- MÉTODO CORREGIDO ---
    return this.http.put<StateResponse>(`${API_URL}/${id}`, { name: name }).pipe(
      map(response => response.data) // Desenvuelve el item único
    );
  }

  deleteState(id: number): Observable<any> {
    return this.http.delete(`${API_URL}/${id}`);
  }
}