// en src/app/features/profile/profile.component.ts (CÓDIGO FINAL Y ROBUSTO)

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Observable, of } from 'rxjs';
import { switchMap, map, catchError, tap } from 'rxjs/operators';
import { User } from '../../core/models/user.model';
import { ProfileService } from './services/profile.service';
import { AuthService } from '../auth/services/auth.service';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterLink], 
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  private profileService = inject(ProfileService);
  private route = inject(ActivatedRoute);
  private authService = inject(AuthService);

  userProfile$: Observable<User | null>; // Permitimos que sea nulo en caso de error
  isLoading = true; // Añadimos una variable de carga manejada manualmente
  error: string | null = null; // Para mostrar mensajes de error
  isOwnProfile: boolean = false;
  
  constructor() {
    // Inicializamos el observable para evitar errores
    this.userProfile$ = of(null);
  }

  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();

    this.route.paramMap.pipe(
      tap(() => {
        // Reiniciamos los estados al empezar una nueva carga
        this.isLoading = true;
        this.error = null;
      }),
      switchMap(params => {
        const usernameFromUrl = params.get('username');
        if (usernameFromUrl) {
          this.isOwnProfile = currentUser?.username === usernameFromUrl;
          return this.profileService.getPublicProfile(usernameFromUrl);
        } else {
          this.isOwnProfile = true;
          return this.profileService.getOwnProfile();
        }
      }),
      map((response: any) => response.data),
      catchError(error => {
        console.error('Error al cargar el perfil:', error);
        this.error = 'No se pudo cargar el perfil. Puede que el usuario no exista o haya un problema de conexión.';
        return of(null); // Devolvemos nulo en caso de error
      })
    ).subscribe(profileData => {
      this.userProfile$ = of(profileData);
      this.isLoading = false; // Detenemos la carga, tanto en éxito como en error.
    });
  }
}