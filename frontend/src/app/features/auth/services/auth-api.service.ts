/**
 * @file auth-api.service.ts
 * @description Servicio dedicado a la comunicación HTTP con los endpoints de autenticación del backend (Login/Register).
 */
import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthToken } from './auth.service'; // Importamos la interfaz del token

// Nota: Debes definir las interfaces para las peticiones (ej. LoginCredentials, RegisterData)
interface LoginCredentials {
  email: string;
  password: string;
}

/**
 * @class AuthApiService
 * @description Inyectable para manejar las peticiones a la API de autenticación.
 */
@Injectable({
  providedIn: 'root'
})
export class AuthApiService {
  // Aquí debes definir tu URL base de la API. Asumo que es el puerto 8000 de tu backend Docker.
  private readonly API_URL = 'http://localhost:8000/api/auth';
  
  private http = inject(HttpClient);

  /**
   * @brief Envía las credenciales de usuario al endpoint de login del backend.
   * @param {LoginCredentials} credentials - Objeto que contiene el email y la contraseña.
   * @returns {Observable<AuthToken>} Un observable que emite el objeto AuthToken en caso de éxito.
   */
  login(credentials: LoginCredentials): Observable<AuthToken> {
    // La ruta de login en tu backend Laravel es /api/auth/login
    return this.http.post<AuthToken>(`${this.API_URL}/login`, credentials);
  }

  // Aquí se podrían añadir métodos para register, refresh, etc.
}