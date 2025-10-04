/**
 * @file jwt.interceptor.ts
 * @description Functional interceptor to attach the JWT access token to all outgoing HTTP requests.
 * This is a key component of security best practices in Angular.
 */

import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpEvent } from '@angular/common/http';
import { inject } from '@angular/core';
import { Observable } from 'rxjs';

import { AuthService } from '../../features/auth/services/auth.service';

/**
 * @brief Functional interceptor for injecting the JWT into request headers.
 * @description Uses `AuthService.getAccessToken()` to retrieve the token and attaches it as
 * 'Bearer <token>' in the 'Authorization' header for all outgoing requests.
 *
 * @param {HttpRequest<unknown>} req The outgoing HTTP request.
 * @param {HttpHandlerFn} next The next interceptor/handler in the chain.
 * @returns {Observable<HttpEvent<unknown>>} An Observable of HTTP events.
 */
export const jwtInterceptor: HttpInterceptorFn = (
  req: HttpRequest<unknown>,
  next: HttpHandlerFn
): Observable<HttpEvent<unknown>> => {

  // Inject the authentication service
  const authService = inject(AuthService);

  // Get the access token
  const accessToken = authService.getAccessToken(); //

  // If the token exists, clone the request and add the header
  if (accessToken) {
    // Clone the immutable request object to modify headers
    const cloned = req.clone({
      setHeaders: {
        // Standard format for JWT Bearer tokens (RFC 6750)
        Authorization: `Bearer ${accessToken}` 
      }
    });

    // Pass the cloned request (with the token) to the next handler
    return next(cloned);
  }

  // If there is no token, pass the original request unchanged.
  return next(req);
};