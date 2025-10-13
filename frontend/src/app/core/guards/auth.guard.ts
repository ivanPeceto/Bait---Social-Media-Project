/**
 * @file auth.guard.ts
 * @brief Route Guard to protect private routes that require user authentication.
 * @details This guard is implemented as a functional guard (CanActivateFn). It synchronously 
 * checks the user's session status using AuthService.isAuthenticated(). If the user is 
 * not authenticated, it ensures a clean logout state and redirects them to the login route.
 */
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../../features/auth/services/auth.service';

/**
 * @brief Functional Guard implementation for checking authentication status.
 * @param route The activated route snapshot.
 * @param state The router state snapshot.
 * @returns {boolean | UrlTree} True if the user is authenticated, otherwise redirects via a UrlTree.
 */
export const AuthGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.isAuthenticated()) {
    return true; 
  } else {
    authService.logout(); 
    return router.createUrlTree(['/auth/login']); 
  }
};