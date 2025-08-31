/**
 * @file app.routes.ts
 * @brief Defines the main routes for the standalone application.
 */

import { Routes } from '@angular/router';

/**
 * @brief Main application routes.
 * @description Defines the routing configuration for the application, including lazy-loaded modules for authentication and home features.
 */
export const routes: Routes = [
    {
        path: 'auth',
        loadChildren: () => import('./features/auth/auth.routes').then(m => m.AUTH_ROUTES)
    },
    {
    path: 'home',
    loadChildren: () => import('./features/home/home.routes').then(m => m.HOME_ROUTES)
    },
    {
        path: '',
        redirectTo: 'home',
        pathMatch: 'full'
    },
    {
        path: '**',
        redirectTo: 'auth'
    }
];