/**
 * @file login.ts
 * @brief Component responsible for user authentication via login credentials.
 * @details This component manages the reactive form validation, handles user input, 
 * and initiates the authentication request through the AuthService. It provides 
 * visual feedback for success and authentication errors.
 */

import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { AuthService } from '../services/auth.service'; 

/**
 * @class LoginComponent
 * @description Manages the login form, state, and submission process. 
 * It relies on AuthService for HTTP communication and session management.
 * @Component
 */
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, CommonModule],
  templateUrl: './login.html', 
  styleUrls: ['./login.scss'] 
})
export default class LoginComponent {

  private authService = inject(AuthService);
  private router = inject(Router);

  /**
   * @public
   * @property {string | null} errorMessage - Stores the specific error message to be displayed to the user.
   */
  public errorMessage: string | null = null;

  /**
   * @public
   * @property {FormGroup} loginForm - Defines the structure and validation rules for the login form.
   */
  public loginForm = new FormGroup({
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)])
  });

  /**
   * @brief Handles the login form submission event.
   * @description Performs form validation, calls the AuthService's login method, 
   * and handles API responses by either redirecting on success or displaying an error message.
   * @returns {void}
   */
  public onSubmit(): void {
    this.errorMessage = null;

    if (this.loginForm.invalid) {
      this.loginForm.markAllAsTouched();
      return;
    }

    const credentials = this.loginForm.value;

    // Calls AuthService's login method, which handles the HTTP request and session saving.
    this.authService.login(credentials as any).subscribe({
      next: () => {
        // Success: Redirect to the main protected application route.
        this.router.navigate(['/']); 
      },
      error: (err) => {
        // Error Handling based on HTTP status code.
        if (err.status === 401) {
          this.errorMessage = 'Credenciales inválidas. Verifica tu email y contraseña.';
        } else if (err.status >= 400) {
          this.errorMessage = 'Error en el inicio de sesión. Inténtalo de nuevo.';
        } else {
          this.errorMessage = 'Error de conexión. Verifica tu conexión.';
        }
        console.error('Login Error:', err);
      }
    });
  }
}