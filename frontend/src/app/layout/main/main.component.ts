// en src/app/layout/main/main.component.ts (o la ruta donde esté)

import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink } from '@angular/router';
import { AuthService } from '../../features/auth/services/auth.service';
import { User } from '../../core/models/user.model'; // Asegúrate de que esta ruta sea correcta

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  templateUrl: './main.component.html',
})
export class MainComponent implements OnInit { // Implementamos OnInit
  private authService = inject(AuthService);

  public currentUser: User | any | null = null; // Para almacenar el usuario actual

  ngOnInit(): void {
    // Carga el usuario al inicializar el componente
    this.currentUser = this.authService.getCurrentUser();
  }

  /// en src/app/layout/main/main.component.ts

// ... (el resto del código: imports, @Component, ngOnInit, etc.)

  /**
   * Verifica si el usuario tiene rol de 'admin' o 'moderator'.
   * VERSIÓN CORREGIDA: Lee el rol como un string simple.
   */
  isPrivilegedUser(): boolean {
    if (!this.currentUser || !this.currentUser.role) {
      return false;
    }
    // Leemos directamente el valor de 'role'
    const roleName = this.currentUser.role; 
    return roleName === 'admin' || roleName === 'moderator';
  }

  logout(): void {
    this.authService.logout();
  }
} // Fin de la clase