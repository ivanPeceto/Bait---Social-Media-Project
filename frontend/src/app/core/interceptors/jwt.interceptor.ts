/**
 * @file jwt.interceptor.ts
 * @brief Interceptor responsible for attaching the JWT access token to outgoing API requests.
 * @details This interceptor reads the stored access token and injects it into the 
 * Authorization header for all authenticated routes. It prevents injecting 
 * the token for public endpoints like login and token refresh to avoid errors.
 */
import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable()
export class JwtInterceptor implements HttpInterceptor {

  constructor(/* private authService: AuthService */) {}

  /**
   * @brief Intercepts an outgoing request and attaches the Authorization header.
   * * @param request The outgoing request object.
   * @param next The next interceptor or the final handler.
   * @returns An Observable of the HTTP events.
   */
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    // 1. Retrieve the access token. 
    const accessToken = localStorage.getItem('accessToken'); 
    
    // 2. Define public URL keywords that should not receive a token.
    const publicUrls = ['login', 'register', 'refresh'];
    const isPublicUrl = publicUrls.some(url => request.url.includes(url));

    // 3. Inject the token if available and the URL is not public.
    if (accessToken && !isPublicUrl) {
      request = request.clone({
        setHeaders: {
          Authorization: `Bearer ${accessToken}`,
        },
      });
    }

    return next.handle(request);
  }
}