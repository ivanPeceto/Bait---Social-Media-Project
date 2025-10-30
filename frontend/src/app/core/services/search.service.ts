import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
export interface UserSearchResult {
  id: number;
  username: string;
  name: string;
  avatar: {
    url_avatars: string;
  };
}
interface SearchByNameResponse {
  data: UserSearchResult[];
}

interface SearchByUsernameResponse {
    data: UserSearchResult;
}

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}/users/search`;

  searchByName(term: string): Observable<UserSearchResult[]> {
    if (!term.trim()) {
      return of([]);
    }
    return this.http.get<SearchByNameResponse>(`${this.apiUrl}/name/${term}`).pipe(
      map(response => response.data), // Extraemos el array 'data'
      catchError(() => of([])) // Si hay un error 404 (no encontrado), devolvemos un array vacío
    );
  }

  /**
   * Busca un usuario por su username exacto.
   * Corresponde a: GET /api/users/search/username/{username}
   */
  searchByUsername(term: string): Observable<UserSearchResult[]> {
    if (!term.trim()) {
      return of([]);
    }
    return this.http.get<SearchByUsernameResponse>(`${this.apiUrl}/username/${term}`).pipe(
      map(response => [response.data]), 
      catchError(() => of([]))
    );
  }
}