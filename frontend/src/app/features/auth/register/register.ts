/**
 * @file register.ts
 * @brief Component responsible for user registration and immediate session creation.
 * @details This component manages the reactive form for new user details, handles 
 * communication with the registration endpoint, and provides specific feedback for 
 * validation and network errors.
 */

import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../services/auth.service';

/**
 * @class RegisterComponent
 * @description Manages the registration form, controls the state of success/failure, 
 * and initiates the user creation request.
 * @Component
 */
@Component({
  selector: 'app-register',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, CommonModule],
  templateUrl: './register.html', 
  styleUrls: ['./register.scss'] 
})

export default class Register{

  private authService = inject(AuthService);
  private router = inject(Router);

  /**
   * @public
   * @property {string | null} errorMessage - Stores the specific API error message, particularly for validation errors.
   */
  public errorMessage: string | null = null;
  
  /**
   * @public
   * @property {boolean} registrationSuccessful - Flag to display success message before redirection.
   */
  public registrationSuccessful: boolean = false; 

  /**
   * @public
   * @property {FormGroup} registerForm - Defines the structure and validation rules for all required registration fields.
   */
  public registerForm = new FormGroup({
    username: new FormControl('', [Validators.required, Validators.minLength(3)]),
    name: new FormControl('', [Validators.required, Validators.minLength(2)]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)])
  });

  /**
   * @brief Handles the registration form submission event.
   * @description Validates the form data, calls the AuthService's register method, 
   * and handles the API response.
   * @returns {void}
   */
  public onSubmit(): void {
    this.errorMessage = null; 
    this.registrationSuccessful = false; 

    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    const data = this.registerForm.value;

    this.authService.register(data as any).subscribe({
      next: () => {
        // Success: Set flag to display message
        this.registrationSuccessful = true; 
        
        // Redirect after a short delay so the user perceives the success message
        setTimeout(() => {
          this.router.navigate(['/']); 
        }, 500); 
      },
      error: (err) => {
        // Error Handling: Extract specific validation errors (422) or display generic error.
        if (err.status === 422 && err.error && err.error.errors) {
          // Extracts and displays the first specific validation error message from the API response
          const validationErrors = err.error.errors;
          const firstKey = Object.keys(validationErrors)[0];
          this.errorMessage = validationErrors[firstKey][0];
        
        } else if (err.status >= 400) {
          // Generic server error message
          this.errorMessage = err.error.message || 'Registration failed. Please check your data.';
        
        } else {
          // Network error message
          this.errorMessage = 'Connection error. Please try again later.';
        }
        console.error('Registration Error:', err);
      }
    });
  }
}