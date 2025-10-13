// app.config.ts

import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptorsFromDi, HTTP_INTERCEPTORS } from '@angular/common/http';
import { routes } from './app.routes'; 
import { AuthErrorInterceptor } from './core/interceptors/auth-error.interceptor'; 
import { JwtInterceptor } from './core/interceptors/jwt.interceptor'; 

export const appConfig: ApplicationConfig = {
  providers: [

    provideRouter(routes), 
    
    provideHttpClient(
      withInterceptorsFromDi()
    ),

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