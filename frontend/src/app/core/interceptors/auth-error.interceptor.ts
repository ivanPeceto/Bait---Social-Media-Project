/**
 * @file auth-error.interceptor.ts
 * @brief Handles HTTP 401 Unauthorized errors to automatically refresh JWT access tokens.
 * @details This interceptor centralizes the logic for token renewal, preventing multiple 
 * simultaneous refresh attempts (race condition) and ensuring subsequent 
 * failed requests are retried with the newly acquired access token.
 */
import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor, HttpErrorResponse, HttpClient } from '@angular/common/http'; 
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { catchError, filter, take, switchMap, finalize } from 'rxjs/operators';


@Injectable()
export class AuthErrorInterceptor implements HttpInterceptor {

  private isRefreshing = false;
  private refreshTokenSubject: BehaviorSubject<any> = new BehaviorSubject<any>(null);

  /**
   * @brief Injects the HttpClient service.
   * @param http Provides HTTP methods necessary for token renewal.
   */
  constructor(private http: HttpClient /*, private authService: AuthService, private router: Router */) {}

  /**
   * @brief Intercepts the HTTP response stream to catch 401 errors.
   * * @param request The outgoing request object.
   * @param next The next interceptor or the final handler.
   * @returns An Observable of the HTTP events.
   */
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(
      catchError((error) => {
        if (error instanceof HttpErrorResponse && error.status === 401) {
          if (this.isRefreshAttempt(request.url)) {
            this.handleLogout();
            return throwError(() => error);
          }

          return this.handle401Error(request, next);
        }
        return throwError(() => error);
      })
    );
  }

  /**
   * @brief Manages the 401 error process, triggering token renewal or queuing requests.
   * @param request The failed request object.
   * @param next The next handler in the pipeline.
   * @returns A stream that will either retry the request or force logout.
   */
  private handle401Error(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (!this.isRefreshing) {
      this.isRefreshing = true;
      this.refreshTokenSubject.next(null); 

      const refreshToken = localStorage.getItem('refreshToken'); 

      if (!refreshToken) {
        this.handleLogout();
        return throwError(() => new Error('Refresh token missing. Cannot renew.'));
      }


      return this.callRefreshEndpoint(refreshToken).pipe(
        switchMap((response: any) => {
          this.isRefreshing = false;
          const newAccessToken = response.access_token;
          
          localStorage.setItem('accessToken', newAccessToken); 
          
          this.refreshTokenSubject.next(newAccessToken);


          return next.handle(this.addToken(request, newAccessToken));
        }),
        catchError((err) => {
          this.isRefreshing = false;
          this.handleLogout(); 
          return throwError(() => err);
        }),
        finalize(() => {
          this.isRefreshing = false;
        })
      );
    } else {
      return this.refreshTokenSubject.pipe(
        filter(token => token !== null), 
        take(1), 
        switchMap(token => {
          return next.handle(this.addToken(request, token));
        })
      );
    }
  }

/**
 * @brief Calls the backend endpoint to exchange the refresh token for a new access token.
 * @param refreshToken The expired refresh token.
 * @returns An Observable with the new token data.
 */
private callRefreshEndpoint(refreshToken: string): Observable<any> { 

  const refreshUrl = 'YOUR_API_BASE_URL/api/auth/refresh'; 

  return this.http.post(refreshUrl, { refresh_token: refreshToken });
}

  /**
   * @brief Clones the request and adds the Authorization header.
   * @param request The request to clone.
   * @param token The JWT access token.
   * @returns The cloned request with the header.
   */
  private addToken(request: HttpRequest<any>, token: string): HttpRequest<any> {
    return request.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`,
      },
    });
  }

  /**
   * @brief Identifies if the given URL is the token renewal endpoint.
   * @param url The request URL.
   * @returns True if the URL is the refresh endpoint.
   */
  private isRefreshAttempt(url: string): boolean {
    const refreshUrlKeyword = 'refresh_token'; 
    return url.includes(refreshUrlKeyword);
  }
  
  /**
   * @brief Cleans up tokens and redirects to the login page.
   */
  private handleLogout(): void {
    localStorage.removeItem('accessToken');
    localStorage.removeItem('refreshToken');
  }
}