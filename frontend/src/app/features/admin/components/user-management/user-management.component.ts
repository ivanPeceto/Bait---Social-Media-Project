import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminUserService, User } from '../../services/admin-user.service';

@Component({
  selector: 'app-user-management',
  standalone: true, // Lo hacemos standalone para facilitar las importaciones
  imports: [CommonModule],
  templateUrl: './user-management.component.html',
})
export class UserManagementComponent implements OnInit {
  private adminUserService = inject(AdminUserService);
  
  public users: User[] = [];
  public isLoading = true; // Variable para mostrar un mensaje de "Cargando..."
  public error: string | null = null; // Variable para mostrar errores

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.isLoading = true;
    this.error = null;
    this.adminUserService.getUsers().subscribe({
      next: (data) => {
        this.users = data;
        this.isLoading = false;
      },
      error: (err) => {
        console.error("Error cargando usuarios:", err);
        this.error = "No se pudieron cargar los usuarios. Revisa la consola para más detalles.";
        this.isLoading = false;
      }
    });
  }

  onSuspend(user: User): void {
    if (confirm(`¿Estás seguro de que quieres suspender a ${user.username}?`)) {
      this.adminUserService.suspendUser(user.id).subscribe({
        next: () => this.loadUsers(), // Recargamos la lista para ver el cambio
        error: (err) => alert(`Error al suspender: ${err.message}`)
      });
    }
  }

  onActivate(user: User): void {
    if (confirm(`¿Estás seguro de que quieres activar a ${user.username}?`)) {
        this.adminUserService.activateUser(user.id).subscribe({
        next: () => this.loadUsers(), // Recargamos la lista
        error: (err) => alert(`Error al activar: ${err.message}`)
      });
    }
  }
}