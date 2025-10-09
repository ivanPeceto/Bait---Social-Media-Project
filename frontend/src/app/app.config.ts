// app.config.ts

import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptorsFromDi, HTTP_INTERCEPTORS } from '@angular/common/http';
import { routes } from './app.routes'; 
import { AuthErrorInterceptor } from './core/interceptors/auth-error.interceptor'; 
import { JwtInterceptor } from './core/interceptors/jwt.interceptor'; 

export const appConfig: ApplicationConfig = {
  providers: [
    // La constante 'routes' aquí se resuelve con el array importado de app.routes.ts
    provideRouter(routes), 
    
    // 1. Provee el cliente HTTP y habilita el uso de interceptores basados en DI
    provideHttpClient(
      withInterceptorsFromDi()
    ),

    // 2. Registra los interceptores en el orden correcto
    {
      provide: HTTP_INTERCEPTORS,
      useClass: JwtInterceptor,
      multi: true,
    },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthErrorInterceptor,
      multi: true,
    }
  ]
};