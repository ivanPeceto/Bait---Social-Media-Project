/**
 * @file app.routes.ts
 * @description Defines the main application routes, separating public paths (auth) 
 * from protected paths (main content) using the authentication guard.
 */

import { Routes } from '@angular/router';

// import { authGuard } from './core/guards/auth.guard'; 

/**
 * @const routes
 * @description Definition of the application's main routes.
 */
export const routes: Routes = [
  {
    path: 'auth',
    loadChildren: () => import('./features/auth/auth.routes').then(m => m.AUTH_ROUTES)
  },

  // 2. PROTECTED ROUTES (Main Application Content)
  {
    path: '', 
    /**
     * @property {CanActivateFn[]} canActivate - Applies the authGuard to this route and its children.
     * If the guard returns false or a UrlTree, navigation is canceled or redirected.
     */
    loadChildren: () => import('./features/home/home.routes').then(m => m.HOME_ROUTES)
  },
  
  {
    path: '**',
    redirectTo: '' // Redirects unmatched paths to the base route (which is protected)
  }
];