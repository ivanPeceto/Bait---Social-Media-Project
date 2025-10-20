// en src/app/features/search/services/search.service.ts

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { environment } from '../../../../environments/environment';

// Definimos una interfaz para el usuario que coincida con la respuesta de la API
export interface UserSearchResult {
  id: number;
  username: string;
  name: string;
  avatar: {
    url_avatars: string;
  };
}

// Interfaz para la respuesta del endpoint de búsqueda por nombre (que es paginada)
interface SearchByNameResponse {
  data: UserSearchResult[];
}

// Interfaz para la respuesta del endpoint de búsqueda por username (que devuelve un solo objeto)
interface SearchByUsernameResponse {
    data: UserSearchResult;
}

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}/users/search`;

  /**
   * Busca usuarios cuyo nombre contenga el término de búsqueda.
   * Corresponde a: GET /api/users/search/name/{name}
   */
  searchByName(term: string): Observable<UserSearchResult[]> {
    if (!term.trim()) {
      return of([]); // Si el término está vacío, no hacemos nada
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
      // La API devuelve un solo objeto, lo convertimos en un array de un solo elemento para ser consistentes
      map(response => [response.data]), 
      catchError(() => of([]))
    );
  }
}