import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AdminUserService, User } from '../../../../core/services/admin-user.service';
import { AdminStateService, UserState } from '../../../../core/services/admin-state.service';
import { AdminRoleService, UserRole } from '../../../../core/services/admin-role.service';

@Component({
  selector: 'app-user-management',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './user-management.component.html',
})
export class UserManagementComponent implements OnInit {
  private adminUserService = inject(AdminUserService);
  private adminStateService = inject(AdminStateService);
  private adminRoleService = inject(AdminRoleService);
  private fb = inject(FormBuilder);

  public users: User[] = [];
  public allStates: UserState[] = [];
  public allRoles: UserRole[] = [];

  public isLoading = true;
  public error: string | null = null;
  public activeMenuUserId: number | null = null;
  
  public selectedUser: User | null = null;
  public isEditModalOpen = false;
  public isPasswordModalOpen = false;

  public editUserForm: FormGroup;
  public passwordForm: FormGroup;

  constructor() {
    // --- 👇 MODIFICACIÓN AQUÍ: Añadimos 'email' al formulario de edición ---
    this.editUserForm = this.fb.group({
      name: ['', Validators.required],
      username: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]] // Campo de email añadido
    });

    this.passwordForm = this.fb.group({
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required]
    });
  }

  ngOnInit(): void {
    this.loadInitialData();
  }

  loadInitialData(): void {
    this.isLoading = true;
    this.adminUserService.getUsers().subscribe(data => {
        this.users = data;
        this.isLoading = false;
    });
    this.adminStateService.getStates().subscribe(data => this.allStates = data);
    this.adminRoleService.getRoles().subscribe(data => this.allRoles = data);
  }

  toggleActionMenu(userId: number): void {
    this.activeMenuUserId = this.activeMenuUserId === userId ? null : userId;
  }

  openEditModal(user: User): void {
    this.selectedUser = user;
    // --- 👇 MODIFICACIÓN AQUÍ: Rellenamos el campo de email en el formulario ---
    this.editUserForm.patchValue({ 
      name: user.name, 
      username: user.username,
      email: user.email 
    });
    this.isEditModalOpen = true;
    this.activeMenuUserId = null;
  }
  
  openPasswordModal(user: User): void {
    this.selectedUser = user;
    this.isPasswordModalOpen = true;
    this.activeMenuUserId = null;
  }

  closeModals(): void {
    this.isEditModalOpen = false;
    this.isPasswordModalOpen = false;
    this.selectedUser = null;
    this.passwordForm.reset();
  }

  onUpdateUser(): void {
    if (this.editUserForm.invalid || !this.selectedUser) return;
    
    this.adminUserService.updateUser(this.selectedUser.id, this.editUserForm.value).subscribe({
      next: () => {
        const index = this.users.findIndex(u => u.id === this.selectedUser!.id);
        if (index !== -1) {
          // --- 👇 MODIFICACIÓN AQUÍ: Actualizamos también el email localmente ---
          this.users[index].name = this.editUserForm.value.name;
          this.users[index].username = this.editUserForm.value.username;
          this.users[index].email = this.editUserForm.value.email;
        }
        this.closeModals();
      },
      error: (err) => {
        const errorMessage = err.error?.message || 'Ocurrió un error desconocido.';
        alert(`Error al actualizar: ${errorMessage}`);
      }
    });
  }
  
  onChangePassword(): void {
    if (this.passwordForm.invalid || !this.selectedUser) return;
    const { password, password_confirmation } = this.passwordForm.value;

    if (password !== password_confirmation) {
      alert('Las contraseñas no coinciden.');
      return;
    }
    
    this.adminUserService.changeUserPassword(this.selectedUser.id, password, password_confirmation).subscribe({
      next: () => {
        alert('Contraseña actualizada con éxito.');
        this.closeModals();
      },
      error: (err) => alert(`Error al cambiar contraseña: ${err.error.message || err.message}`)
    });
  }

  onChangeState(user: User, event: Event): void {
    const select = event.target as HTMLSelectElement;
    const stateId = parseInt(select.value, 10);
    const stateName = select.options[select.selectedIndex].text;

    this.adminUserService.updateUser(user.id, { state_id: stateId }).subscribe({
      next: () => {
        user.state = stateName.toLowerCase();
      },
      error: (err) => {
        alert(`Error al cambiar el estado: ${err.message}`);
        this.loadInitialData();
      }
    });
  }

  onChangeRole(user: User, event: Event): void {
    const select = event.target as HTMLSelectElement;
    const roleId = parseInt(select.value, 10);
    const roleName = select.options[select.selectedIndex].text;

    this.adminUserService.updateUser(user.id, { role_id: roleId }).subscribe({
      next: () => {
        user.role = roleName.toLowerCase();
      },
      error: (err) => {
        alert(`Error al cambiar el rol: ${err.message}`);
        this.loadInitialData();
      }
    });
  }
  
  onDeleteAvatar(user: User): void {
    if (confirm(`¿Seguro que quieres eliminar el AVATAR de ${user.username}?`)) {
      this.adminUserService.deleteUserAvatar(user.id).subscribe(() => alert('Avatar eliminado.'));
    }
    this.activeMenuUserId = null;
  }

  onDeleteBanner(user: User): void {
    if (confirm(`¿Seguro que quieres eliminar el BANNER de ${user.username}?`)) {
      this.adminUserService.deleteUserBanner(user.id).subscribe(() => alert('Banner eliminado.'));
    }
    this.activeMenuUserId = null;
  }
}