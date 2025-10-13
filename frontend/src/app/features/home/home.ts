import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../auth/services/auth.service';

@Component({
  selector: 'app-home',
  standalone: true,
  templateUrl: './home.html',
})
export class Home {
  private authService = inject(AuthService);
  private router = inject(Router);

  logout() {
    this.authService.logout(); // Limpia token / sesión
    this.router.navigate(['/auth/login']); // Redirige al login
  }
}