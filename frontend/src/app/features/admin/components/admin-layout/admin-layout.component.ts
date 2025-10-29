import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../../../core/services/auth.service'; 
import { User } from '../../../../core/models/user.model'; 

@Component({
  selector: 'app-admin-layout',
  standalone: true,
  imports: [ CommonModule, RouterModule ],
  templateUrl: './admin-layout.component.html',
})
export class AdminLayoutComponent implements OnInit { 
  private authService = inject(AuthService); 
  
  currentUser: User | any | null = null; 
  isCurrentUserAdmin: boolean = false; 

  ngOnInit(): void {
    // Esta línea ahora debería funcionar al encontrar AuthService
    this.currentUser = this.authService.getCurrentUser(); 
    
    // El resto de la lógica está bien
    this.isCurrentUserAdmin = !!this.currentUser && this.currentUser.role?.toLowerCase() === 'admin';
    console.log('[AdminLayout] Es Admin:', this.isCurrentUserAdmin); 
  }
}