/**
 * @file auth.service.ts
 * @brief Centralized service for managing user session state and authentication workflow.
 * @details This service is responsible for handling JWT storage in browser's local storage,
 * providing the current authentication status reactively via Observable, and implementing 
 * essential safeguards to prevent Server-Side Rendering (SSR) failures.
 */
import { Injectable, inject, PLATFORM_ID } from '@angular/core'; 
import { isPlatformBrowser } from '@angular/common'; 
import { HttpClient } from '@angular/common/http';
import { Observable, tap, BehaviorSubject } from 'rxjs'; 
import { Router } from '@angular/router';
import { environment } from '../../../../environments/environment';

// NOTE: Interfaces matching the backend API contract are defined here.
interface AuthResponse {
  access_token: string;
  refresh_token?: string;
  expires_in: number;
  user: { 
    id: number; 
    name: string; 
    email: string; 
  };
}

interface Credentials {
  email: string;
  password: string;
}

const API_URL = environment.API_URL; // API base URL for authentication endpoints

/**
 * @class AuthService
 * @description Provides reactive session management and handles API calls for login/register.
 * It centralizes all state management and storage logic.
 */
@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private readonly JWT_TOKEN_KEY = 'accessToken';
  private readonly REFRESH_TOKEN_KEY = 'refreshToken';
  private platformId = inject(PLATFORM_ID);
  
  /**
   * @private
   * @property {BehaviorSubject<boolean>} isLoggedIn - Internal reactive source holding the authentication state.
   * @description Initialized to 'false' on the server (SSR) to prevent errors.
   */
  private isLoggedIn = new BehaviorSubject<boolean>(false); 

  /**
   * @public
   * @property {Observable<boolean>} isLoggedIn$ - Public observable for components and guards to react to session changes.
   */
  public isLoggedIn$: Observable<boolean> = this.isLoggedIn.asObservable(); 

  private http = inject(HttpClient);
  private router = inject(Router); 

  /**
   * @constructor
   * @description Initializes the service. Checks for existing tokens in storage only if running in the browser.
   */
  constructor() {
    // Check authentication status only after the browser environment is confirmed, preventing SSR failures.
    if (isPlatformBrowser(this.platformId)) {
        this.isLoggedIn.next(this.isAuthenticated());
    }
  }

  // ----------------------------------------------------
  // PUBLIC METHODS (API and State Control)
  // ----------------------------------------------------

  /**
   * @brief Attempts to log the user in by calling the backend API.
   * @details If successful, it saves session data and updates the reactive state.
   * @param {Credentials} credentials - The user's email and password.
   * @returns {Observable<AuthResponse>} An Observable of the successful response, containing tokens and user data.
   */
  public login(credentials: Credentials): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${API_URL}/auth/login`, credentials).pipe(
      tap(response => {
        this.saveSessionData(response);
        this.isLoggedIn.next(true);
      })
    );
  }

  /**
   * @brief Registers a new user account by calling the backend API.
   * @details If successful, it performs an automatic login, saves session data, and updates the reactive state.
   * @param {any} userData - User's registration details.
   * @returns {Observable<AuthResponse>} An Observable of the successful response.
   */
  public register(userData: any): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${API_URL}/auth/register`, userData).pipe(
      tap(response => {
        this.saveSessionData(response);
        this.isLoggedIn.next(true);
      })
    );
  }

  /**
   * @brief Clears all authentication data from local storage and redirects the user to the login page.
   */
  public logout(): void {
    this.removeSessionData();
    this.isLoggedIn.next(false);
    this.router.navigate(['/auth/login']);
  }

  /**
   * @brief Retrieves the access token from storage.
   * @details This method is used primarily by the JwtInterceptor to attach the token to request headers.
   * @returns {string | null} The access token string or null, protected against SSR environment access.
   */
  getAccessToken(): string | null {
    if (!isPlatformBrowser(this.platformId)) return null;
    return localStorage.getItem(this.JWT_TOKEN_KEY);
  }
  
  /**
   * @brief Checks if the user is currently authenticated (i.e., if a token is present).
   * @returns {boolean} True if a token exists, false otherwise, protected against SSR.
   */
  isAuthenticated(): boolean {
    if (!isPlatformBrowser(this.platformId)) return false;
    // Simple token presence check
    return !!this.getAccessToken();
  }

  // ----------------------------------------------------
  // PRIVATE METHODS (Storage Handling)
  // ----------------------------------------------------

  /**
   * @private
   * @brief Saves the authentication response data (tokens) to the browser's local storage.
   * @param {AuthResponse} authData - The response object containing tokens and user details.
   */
  private saveSessionData(authData: AuthResponse): void {
    if (!isPlatformBrowser(this.platformId)) return;
    localStorage.setItem(this.JWT_TOKEN_KEY, authData.access_token);
    if (authData.refresh_token) {
        localStorage.setItem(this.REFRESH_TOKEN_KEY, authData.refresh_token);
    }
    localStorage.setItem('currentUser', JSON.stringify(authData.user));
  }

  /**
   * @private
   * @brief Removes all authentication data from local storage.
   */
  private removeSessionData(): void {
    if (!isPlatformBrowser(this.platformId)) return;
    localStorage.removeItem(this.JWT_TOKEN_KEY);
    localStorage.removeItem(this.REFRESH_TOKEN_KEY);
    localStorage.removeItem('currentUser');
  }
}