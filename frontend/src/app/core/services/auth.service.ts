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
import { environment } from '../../../environments/environment';
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

const API_URL = `${environment.apiUrl}/auth`;

/**
 * @class AuthService
 * @description Provides reactive session management and handles API calls for login/register.
 * It centralizes all state management and storage logic.
 */
@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly JWT_TOKEN_KEY = 'accessToken';
  private readonly REFRESH_TOKEN_KEY = 'refreshToken';
  private platformId = inject(PLATFORM_ID);

  private isLoggedIn = new BehaviorSubject<boolean>(false);
  public isLoggedIn$: Observable<boolean> = this.isLoggedIn.asObservable();
  private currentUser$ = new BehaviorSubject<any>(null);
  public readonly currentUserChanges$: Observable<any> = this.currentUser$.asObservable();

  private http = inject(HttpClient);
  private router = inject(Router);

  constructor() {
    if (isPlatformBrowser(this.platformId)) {
      this.isLoggedIn.next(this.isAuthenticated());
      // Initialize currentUser$ from localStorage on app start
      const existing = localStorage.getItem('currentUser');
      if (existing) {
        try {
          this.currentUser$.next(JSON.parse(existing));
        } catch { }
      }
    }
  }

  public login(credentials: Credentials): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${API_URL}/login`, credentials).pipe(
      tap((response) => {
        this.saveSessionData(response);
        this.isLoggedIn.next(true);
        this.currentUser$.next(response.user);
      })
    );
  }

  public register(userData: any): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${API_URL}/register`, userData).pipe(
      tap((response) => {
        this.saveSessionData(response);
        this.isLoggedIn.next(true);
        this.currentUser$.next(response.user);
      })
    );
  }

  public updateCurrentUser(user: any): void {
    if (!isPlatformBrowser(this.platformId)) return;
    localStorage.setItem('currentUser', JSON.stringify(user));
    this.currentUser$.next(user);
  }

  public logout(): void {
    this.removeSessionData();
    this.isLoggedIn.next(false);
    this.currentUser$.next(null);
    this.router.navigate(['/auth/login']);
  }

  getAccessToken(): string | null {
    if (!isPlatformBrowser(this.platformId)) return null;
    return localStorage.getItem(this.JWT_TOKEN_KEY);
  }

  isAuthenticated(): boolean {
    if (!isPlatformBrowser(this.platformId)) return false;
    return !!this.getAccessToken();
  }

  public getCurrentUser(): any | null {
    return this.currentUser$.getValue();
  }

  private saveSessionData(authData: AuthResponse): void {
    if (!isPlatformBrowser(this.platformId)) return;

    localStorage.setItem(this.JWT_TOKEN_KEY, authData.access_token);
    if (authData.refresh_token) {
      localStorage.setItem(this.REFRESH_TOKEN_KEY, authData.refresh_token);
    }

    localStorage.setItem('currentUser', JSON.stringify(authData.user));
    this.currentUser$.next(authData.user);

    localStorage.setItem('jwt', authData.access_token);
    localStorage.setItem('user_id', authData.user.id.toString());
  }

  private removeSessionData(): void {
    if (!isPlatformBrowser(this.platformId)) return;
    localStorage.removeItem(this.JWT_TOKEN_KEY);
    localStorage.removeItem(this.REFRESH_TOKEN_KEY);
    localStorage.removeItem('currentUser');
  }
}
