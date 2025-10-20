import { Routes } from '@angular/router';
<<<<<<< HEAD
import { ProfileComponent } from './features/profile/profile.component';
import { AuthGuard } from './core/guards/auth.guard'; 
import { MainComponent } from './layout/main/main.component';
=======

import { AuthGuard } from './core/guards/auth.guard';
import { adminGuard } from './core/guards/admin.guard'; // <-- Asegúrate de importar el adminGuard
>>>>>>> origin/feature/frontend/search

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

<<<<<<< HEAD
{
    path: '',
    component: MainComponent,
=======
  // --- Rutas protegidas por login ---
  {
    path: '',
>>>>>>> origin/feature/frontend/search
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
<<<<<<< HEAD
=======
  {
    path: 'profile',
    canActivate: [AuthGuard],
    loadComponent: () =>
      import('./features/profile/profile.component').then(
        (m) => m.ProfileComponent
      ),
  },

  {
    path: 'profile/:username', // <-- ¡Añadimos el parámetro :username!
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
>>>>>>> origin/feature/frontend/search
];