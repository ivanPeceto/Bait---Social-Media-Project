import { Component, inject } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';

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
export class Register {
  private fb = inject(FormBuilder);

  // Formulario actualizado solo con los campos solicitados
  registerForm = this.fb.group({
    name: ['', [Validators.required]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  onSubmit() {
    if (this.registerForm.valid) {
      // Lógica de maqueta: solo muestra en consola
      console.log('Formulario de registro enviado:', this.registerForm.value);
      alert('¡Revisa la consola para ver los datos!');
    } else {
      // Marca los campos como "tocados" para mostrar errores de validación si los hubiera
      this.registerForm.markAllAsTouched();
    }
  }
}