// src/app/core/guards/admin.guard.ts

import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

/**
 * @brief Guarda para proteger rutas de administración (/admin).
 * @details Este guard verifica no solo que el usuario esté autenticado,
 * sino que también tenga el rol de 'admin' o 'moderator'.
 */
export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Primero, chequeamos si está autenticado (como tu AuthGuard)
  if (!authService.isAuthenticated()) {
    authService.logout();
    return router.createUrlTree(['/auth/login']);
  }

  // Segundo, si está autenticado, obtenemos su información y verificamos el rol
  const currentUserString = localStorage.getItem('currentUser');
  if (currentUserString) {
    const user = JSON.parse(currentUserString);
    // Asumiendo que la estructura del usuario desde la API es user.role.name_user_roles
    if (user.role && (user.role === 'admin' || user.role === 'moderator')) {
      return true; // ¡Acceso permitido!
    }
  }

  // Si no cumple con el rol, lo mandamos al home.
  // No hacemos logout, porque es un usuario válido, solo que no es admin.
  return router.createUrlTree(['/home']);
};