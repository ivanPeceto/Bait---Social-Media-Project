import { Routes } from '@angular/router';

import { AuthGuard } from './core/guards/auth.guard';
import { adminGuard } from './core/guards/admin.guard'; // <-- Asegúrate de importar el adminGuard

/**
 * @const routes
 * @description Definition of the application's main routes.
 */
export const routes: Routes = [
  // --- Ruta pública de autenticación ---
  {
    path: 'auth',
    loadChildren: () => import('./features/auth/auth.routes').then(m => m.AUTH_ROUTES)
  },

  // --- Rutas protegidas por login ---
  {
    path: '',
    canActivate: [AuthGuard],
    loadChildren: () => import('./features/home/home.routes').then(m => m.HOME_ROUTES)
  },
  {
    path: 'profile',
    canActivate: [AuthGuard],
    loadComponent: () =>
      import('./features/profile/profile.component').then(
        (m) => m.ProfileComponent
      ),
  },

  // ====================================================================
  // =====> AÑADIMOS LA RUTA QUE FALTABA PARA EL PANEL DE ADMIN <=====
  // ====================================================================
  {
    path: 'admin',
    canActivate: [adminGuard], // Primero, protege la ruta
    // Luego, carga perezosamente todo el módulo de Administración que creamos
    loadChildren: () => import('./features/admin/admin.module').then(m => m.AdminModule)
  },
  // ====================================================================

  // --- Ruta comodín al final ---
  {
    path: '**',
    redirectTo: ''
  }
];