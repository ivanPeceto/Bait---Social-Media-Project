// /frontend/src/app/app.config.ts (MODIFICADO)

import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http'; // Importaciones necesarias

import { routes } from './app.routes'; 

// Importamos los interceptores
//import { jwtInterceptor } from './core/interceptors/jwt.interceptor';
//import { authErrorInterceptor } from './core/interceptors/auth-error.interceptor';

/**
 * @const appConfig
 * @description Configuración principal de la aplicación Angular Standalone.
 * Registra el sistema de routing y configura el cliente HTTP con los interceptores.
 */
export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    
    /**
     * @brief Configuración del cliente HTTP con interceptores.
     * @description Se registran los interceptores de forma funcional (nueva práctica).
     * El orden es importante para las respuestas (se ejecutan en orden inverso).
     */
    provideHttpClient(
      withInterceptors([
        //authErrorInterceptor, // 1. Se ejecuta de último en la respuesta para manejar el 401.
        //jwtInterceptor        // 2. Se ejecuta de primero en la petición para inyectar el token.
      ])
    )
  ]
};