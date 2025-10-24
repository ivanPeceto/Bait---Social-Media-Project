import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink } from '@angular/router';
import { AuthService } from '../../features/auth/services/auth.service';
import { User } from '../../core/models/user.model';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  templateUrl: './main.component.html',
})
export class MainComponent implements OnInit { 
  private authService = inject(AuthService);

  public currentUser: User | any | null = null; 

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
  }
  isPrivilegedUser(): boolean {
    if (!this.currentUser || !this.currentUser.role) {
      return false;
    }
    const roleName = this.currentUser.role; 
    return roleName === 'admin' || roleName === 'moderator';
  }

  logout(): void {
    this.authService.logout();
  }
}