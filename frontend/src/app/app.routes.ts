import { Routes } from '@angular/router';
import { AuthGuard } from './core/guards/auth.guard'; 
import { MainComponent } from './layout/main/main.component';
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

  {
    path: '',
    component: MainComponent,
    // --- Rutas protegidas por login dentro del layout principal (MainComponent) ---
    canActivate: [AuthGuard], // Protege todas las rutas hijas
    children: [
      {
        path: '', 
        loadComponent: () => import('./features/home/home'),
      },
      {
        path: 'profile', // Perfil del usuario logueado (ej: /profile)
        loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)
      },
      // Ruta para perfil público (por ID o por username)
      {
        path: 'profile/:id', // La ruta debe usar :id o :username, pero no ambos como rutas separadas sin resolver conflictos.
        loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)
      },
      {
        path: 'profile/:username', // Mantengo esta ruta ya que parece que se quería usar el username
        loadComponent: () => import('./features/profile/profile.component').then(m => m.ProfileComponent)
      },
    ]
  },

  // ====================================================================
  // =====> RUTA PARA EL PANEL DE ADMIN <=====
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