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
        loadChildren: () => import('./auth/auth.routes').then(m => m.AUTH_ROUTES)
    },
    {
        path: '',
        redirectTo: 'auth',
        pathMatch: 'full'
    },
    {
        path: '**',
        redirectTo: 'auth'
    }
];