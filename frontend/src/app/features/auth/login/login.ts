/**
 * @file login.ts
 * @description Componente principal para la vista de inicio de sesión.
 */

import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { AuthApiService } from '../services/auth-api.service'; // NUEVO
import { AuthService } from '../services/auth.service'; // BASE

/**
 * @class LoginComponent
 * @description Componente para manejar el formulario de inicio de sesión y la autenticación.
 * @Component
 */
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, CommonModule],
  templateUrl: './login.html', //
  styleUrls: ['./login.scss'] //
})
export default class LoginComponent {

  // Dependencias Inyectadas
  private authApiService = inject(AuthApiService);
  private authService = inject(AuthService);
  private router = inject(Router);

  /**
   * @public
   * @property {boolean} loginError - Bandera para controlar si hubo un error de credenciales.
   */
  public loginError: boolean = false;

  /**
   * @public
   * @property {FormGroup} loginForm - Definición del Reactive Form para el login.
   */
  public loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)])
  });

  /**
   * @brief Maneja el envío del formulario de inicio de sesión.
   * @description Llama a la API de autenticación y, en caso de éxito, registra el token
   * y redirige al usuario a la página principal.
   * @returns {void}
   */
  public onSubmit(): void {
    this.loginError = false;

    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const credentials = this.loginForm.value;

    // 1. Llamar al servicio API
    this.authApiService.login(credentials as any).subscribe({
      next: (token) => {
        // 2. Éxito: Registrar el token en el AuthService
        this.authService.login(token); // Almacena el token y actualiza isLoggedIn$
        
        // 3. Redirigir al home (ruta protegida). El Guard ya dejará pasar.
        this.router.navigate(['/']); 
      },
      error: (err) => {
        // 4. Error: Si el backend devuelve 401 o 400 (credenciales inválidas)
        if (err.status === 401 || err.status === 400) {
          this.loginError = true;
          console.error('Error de credenciales:', err.error);
        } else {
          // Manejar otros errores (e.g., error de red, 500)
          console.error('Error de login no manejado:', err);
        }
      }
    });
  }
}