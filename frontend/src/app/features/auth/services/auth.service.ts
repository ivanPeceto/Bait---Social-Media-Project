/**
 * @file auth.service.ts
 * @description This file contains the centralized authentication service for the application.
 * It manages the user's login state, as well as the storage and retrieval of JWT tokens.
 */

import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

/**
 * @interface AuthToken
 * @description Defines the structure of the token object received from the API and stored locally.
 */
export interface AuthToken {
  /**
   * @property {string} access_token - The JWT used to authenticate API requests.
   */
  access_token: string;
  /**
   * @property {string} token_type - The type of token, typically 'Bearer'.
   */
  token_type: string;
  /**
   * @property {number} expires_in - The time in seconds until the access token expires.
   */
  expires_in: number;
}

/**
 * @class AuthService
 * @description Injectable service to manage authentication state and JWT tokens.
 * It provides a reactive way for components to be aware of the authentication status.
 * @Injectable
 */
@Injectable({
  providedIn: 'root'
})
export class AuthService {

  /**
   * @private
   * @readonly
   * @property {string} JWT_TOKEN_KEY - The key used to store the AuthToken object in localStorage.
   */
  private readonly JWT_TOKEN_KEY = 'bait_jwt_token';

  /**
   * @private
   * @property {BehaviorSubject<boolean>} isLoggedIn - Private BehaviorSubject that holds the current authentication state.
   */
  private isLoggedIn = new BehaviorSubject<boolean>(this.hasToken());

  /**
   * @public
   * @property {Observable<boolean>} isLoggedIn$ - Public Observable of the authentication state. Components subscribe to it to react to login/logout changes.
   */
  public isLoggedIn$: Observable<boolean> = this.isLoggedIn.asObservable();

  /**
   * @constructor
   * @description Initializes the service. The initial state of isLoggedIn is set in the property's own constructor.
   */
  constructor() { }

  /**
   * @brief Handles a successful login.
   * @description Stores the JWT in localStorage and updates the authentication state to `true`, notifying all subscribed components.
   * @param {AuthToken} token - The token object received from the API after a successful login.
   * @returns {void}
   */
  login(token: AuthToken): void {
    localStorage.setItem(this.JWT_TOKEN_KEY, JSON.stringify(token));
    this.isLoggedIn.next(true);
  }

  /**
   * @brief Handles the user logout process.
   * @description Removes the JWT from localStorage and updates the authentication state to `false`, notifying all subscribed components.
   * @returns {void}
   */
  logout(): void {
    localStorage.removeItem(this.JWT_TOKEN_KEY);
    this.isLoggedIn.next(false);
  }

  /**
   * @brief Retrieves the full token object from localStorage.
   * @returns {AuthToken | null} The AuthToken object if it exists, or `null` if no token is stored.
   */
  getToken(): AuthToken | null {
    const tokenString = localStorage.getItem(this.JWT_TOKEN_KEY);
    if (tokenString) {
      return JSON.parse(tokenString) as AuthToken;
    }
    return null;
  }
  
  /**
   * @brief Helper method to get only the `access_token`.
   * @description This method is particularly useful for HTTP interceptors that need to attach the 'Bearer' token to request headers.
   * @returns {string | null} The access_token string if it exists, otherwise `null`.
   */
  getAccessToken(): string | null {
    return this.getToken()?.access_token || null;
  }

  /**
   * @private
   * @brief Checks if a token exists in localStorage.
   * @description Private helper method to determine the initial authentication state.
   * @returns {boolean} `true` if the token exists, `false` otherwise.
   */
  private hasToken(): boolean {
    return !!localStorage.getItem(this.JWT_TOKEN_KEY);
  }
}