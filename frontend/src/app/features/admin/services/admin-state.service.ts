import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

export interface UserState {
  id: number;
  name: string;
}

interface StatesResponse {
  data: UserState[];
}

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
      map(response => response.data) 
    );
  }

  createState(name: string): Observable<UserState> {
    // --- MÉTODO CORREGIDO ---
    return this.http.post<StateResponse>(API_URL, { name: name }).pipe(
      map(response => response.data) 
    );
  }

  updateState(id: number, name: string): Observable<UserState> {
    // --- MÉTODO CORREGIDO ---
    return this.http.put<StateResponse>(`${API_URL}/${id}`, { name: name }).pipe(
      map(response => response.data) 
    );
  }

  deleteState(id: number): Observable<any> {
    return this.http.delete(`${API_URL}/${id}`);
  }
}