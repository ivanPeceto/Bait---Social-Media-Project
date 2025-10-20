/**
 * @file app.routes.ts
 * @description Defines the main application routes, separating public paths (auth) 
 * from protected paths (main content) using the authentication guard.
 */

import { Routes } from '@angular/router';
import { ProfileComponent } from './features/profile/profile.component';
import { AuthGuard } from './core/guards/auth.guard'; 
import { MainComponent } from './layout/main/main.component';

/**
 * @const routes
 * @description Definition of the application's main routes.
 */
export const routes: Routes = [
  {
    path: 'auth',
    loadChildren: () => import('./features/auth/auth.routes').then(m => m.AUTH_ROUTES)
  },

{
    path: '',
    component: MainComponent,
    canActivate: [AuthGuard],
    children: [
      {
        path: '', 
        loadComponent: () => import('./features/home/home'),
      },
      {
        path: 'profile', 
        loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)
      },
      {
        path: 'profile/:id', 
        loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)
      },
    ]
  },
];