/**
 * @file auth-error.interceptor.ts
 * @description Functional interceptor to handle HTTP 401 (Unauthorized) error responses.
 * Protected for Server-Side Rendering (SSR) using PLATFORM_ID check.
 */

import { HttpInterceptorFn, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { Router } from '@angular/router';
import { catchError, Observable, throwError } from 'rxjs';

import { AuthService } from '../../features/auth/services/auth.service';

/**
 * @brief Functional interceptor to handle HTTP 401 (Unauthorized) responses.
 * @description Intercepts the response stream, and if the status code is 401, it clears the
 * user session by calling `authService.logout()` and redirects the user to the login page.
 * This critical security logic is only executed in the browser environment to prevent SSR errors.
 *
 * @param {HttpRequest<unknown>} req The outgoing HTTP request.
 * @param {HttpHandlerFn} next The next interceptor/handler in the chain.
 * @returns {Observable<HttpEvent<unknown>>} An Observable that continues the stream or throws an error.
 */
export const authErrorInterceptor: HttpInterceptorFn = (req, next): Observable<HttpEvent<unknown>> => {

  const platformId = inject(PLATFORM_ID);

  // SSR Protection: Skip browser-dependent logic (Router, localStorage access via AuthService) on the server.
  if (!isPlatformBrowser(platformId)) {
    return next(req);
  }

  // Inject necessary services only when running in the browser
  const router = inject(Router);
  const authService = inject(AuthService);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {

      if (error.status === 401) {

        console.error('401 error intercepted: Invalid or expired token. Forcing logout and redirecting.');

        // 1. Clear session by removing the token from localStorage
        authService.logout(); 
        
        // 2. Redirect to the login page
        router.navigate(['/auth/login']);

        // Re-throw the error so the component's subscription receives the failure
        return throwError(() => error);
      }

      // Pass through other errors (400, 403, 500, etc.)
      return throwError(() => error);
    })
  );
};