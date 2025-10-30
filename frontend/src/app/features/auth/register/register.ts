import { Component, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, AbstractControl, ValidationErrors } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../../core/services/auth.service';
import { catchError, of } from 'rxjs';

function passwordMatchValidator(control: AbstractControl): ValidationErrors | null {
  const password = control.get('password');
  const passwordConfirmation = control.get('password_confirmation');

  if (password?.value !== passwordConfirmation?.value) {
    passwordConfirmation?.setErrors({ passwordMismatch: true });
    return { passwordMismatch: true };
  } else {
    if (passwordConfirmation?.hasError('passwordMismatch')) {
        passwordConfirmation.setErrors(null);
    }
    return null;
  }
}

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule, 
    RouterLink
  ],
  templateUrl: './register.html',
  styleUrls: ['./register.scss']
})
export default class Register{
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);

  public registerForm: FormGroup;
  public errorMessage: string | null = null;

  constructor() {
    this.registerForm = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(120)]],
      username: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required]
    }, {
      validators: passwordMatchValidator
    });
  }


  onSubmit(): void {
    if (this.registerForm.invalid) {
      this.registerForm.markAllAsTouched();
      return;
    }

    this.errorMessage = null;


    this.authService.register(this.registerForm.value).pipe(
      catchError(error => {
        console.error('Error en el registro:', error);
        this.errorMessage = error.error?.message || 'Ocurrió un error inesperado. Inténtalo de nuevo.';
        return of(null); 
      })
    ).subscribe(response => {
      if (response) {
        this.router.navigate(['/auth/login']);
      }
    });
  }

  get name() { return this.registerForm.get('name'); }
  get email() { return this.registerForm.get('email'); }
  get password() { return this.registerForm.get('password'); }
  get password_confirmation() { return this.registerForm.get('password_confirmation'); }
}