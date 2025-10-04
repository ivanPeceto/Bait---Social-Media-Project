/**
 * @file auth.guard.ts
 * @description Functional route guard to protect routes that should only be accessible
 * by authenticated users.
 */

import { inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { CanActivateFn, Router, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { AuthService } from '../../features/auth/services/auth.service';

/**
 * @brief Authentication Route Guard (CanActivateFn).
 * @description Determines if a route can be activated. It checks the user's authentication
 * status and handles Server-Side Rendering (SSR) by allowing rendering on the server
 * but enforcing the check on the browser.
 *
 * @returns {Observable<boolean | UrlTree>} Observable that emits `true` (allow access)
 * or a `UrlTree` (redirect to login).
 */
export const authGuard: CanActivateFn = ():
  | Observable<boolean | UrlTree>
  | Promise<boolean | UrlTree>
  | boolean
  | UrlTree => {

  const platformId = inject(PLATFORM_ID);
  
  // 1. SSR Protection: If running on the server (Node.js), allow access.
  // This enables pre-rendering of the protected component for better SEO/performance.
  if (!isPlatformBrowser(platformId)) {
      return true; 
  }

  // 2. Browser Logic: Inject services and apply security check.
  const authService = inject(AuthService);
  const router = inject(Router);

  // Subscribe to the reactive login state
  return authService.isLoggedIn$.pipe(
    map((isLoggedIn: boolean) => {
      if (isLoggedIn) {
        // If authenticated, grant access.
        return true;
      } else {
        // If not authenticated, redirect to the login route.
        return router.createUrlTree(['/auth/login']);
      }
    })
  );
};