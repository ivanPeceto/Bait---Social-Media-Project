/**
 * @file public.guard.ts
 * @brief Route Guard to prevent authenticated users from accessing public authentication routes.
 * @details This functional guard prevents a logged-in user from seeing the Login or Register 
 * pages, automatically redirecting them to the application's home route ('/'). It relies on 
 * the reactive state (isLoggedIn$) provided by the AuthService.
 */

import { inject } from '@angular/core';
import { CanActivateFn, Router, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from '../../features/auth/services/auth.service';

/**
 * @brief Functional Guard implementation for checking if the user is already logged in.
 * @returns {Observable<boolean | UrlTree>} Observable that resolves to true if the user is 
 * unauthenticated, or a UrlTree if they are authenticated (redirect to home).
 */
export const publicGuard: CanActivateFn = (): Observable<boolean | UrlTree> => {
  const authService = inject(AuthService);
  const router = inject(Router);


  return authService.isLoggedIn$.pipe(
    map((isLoggedIn: boolean) => {
      if (isLoggedIn) {
        return router.createUrlTree(['/']);
      } else {
        return true;
      }
    })
  );
};