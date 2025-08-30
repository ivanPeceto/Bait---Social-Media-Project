/**
 * @file auth.routes.ts
 * @brief Defines the routes for the standalone authentication feature.
 */

import { Routes } from '@angular/router';
import { Login } from './login/login';
import { Register } from './register/register';

/**
 * @brief Defines the child routes for the authentication feature area.
 * @description These routes handle the login and registration functionalities.
 */
export const AUTH_ROUTES: Routes = [
    { path: 'login', component: Login },
    { path: 'register', component: Register },
    { path: '', redirectTo: 'login', pathMatch: 'full' }
];