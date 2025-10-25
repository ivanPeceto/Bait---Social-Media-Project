// en src/app/features/admin/components/user-management/user-management.component.ts

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AdminUserService, User } from '../../services/admin-user.service';
import { AdminStateService, UserState } from '../../services/admin-state.service';
import { AdminRoleService, UserRole } from '../../services/admin-role.service';
import { AuthService } from '../../../auth/services/auth.service'; 

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
  private authService = inject(AuthService);

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
  public isCurrentUserAdmin: boolean = false;

 
  constructor() {
    this.editUserForm = this.fb.group({
      name: ['', Validators.required],
      username: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
    });
    this.passwordForm = this.fb.group({
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required],
    });
  }

  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    this.isCurrentUserAdmin = !!currentUser && currentUser.role?.toLowerCase() === 'admin';
    this.loadInitialData();
  }

  loadInitialData(): void {
    this.isLoading = true;
    this.error = null;
    this.adminUserService.getUsers().subscribe({
      next: (data) => {
        this.users = data;
        if (this.isCurrentUserAdmin) {
          this.loadRolesAndStates();
        } else {
          this.isLoading = false;
        }
      },
      error: (err) => {
        console.error('Error loading users:', err);
        this.error = 'No se pudieron cargar los usuarios.';
        this.isLoading = false;
      },
    });
  }

  loadRolesAndStates(): void {
    let rolesLoaded = false;
    let statesLoaded = false;
    const checkLoadingComplete = () => {
      if (rolesLoaded && statesLoaded) {
        this.isLoading = false;
      }
    };
    this.adminStateService.getStates().subscribe({
      next: (data) => {
        this.allStates = data;
        statesLoaded = true;
        checkLoadingComplete();
      },
      error: (err) => {
        console.error('Error loading states:', err);
        this.error = (this.error ? this.error + ' / ' : '') + 'No se pudieron cargar los estados.';
        statesLoaded = true;
        checkLoadingComplete();
      },
    });
    this.adminRoleService.getRoles().subscribe({
      next: (data) => {
        this.allRoles = data;
        rolesLoaded = true;
        checkLoadingComplete();
      },
      error: (err) => {
        console.error('Error loading roles:', err);
        this.error = (this.error ? this.error + ' / ' : '') + 'No se pudieron cargar los roles.';
        rolesLoaded = true;
        checkLoadingComplete();
      },
    });
  }

  toggleActionMenu(userId: number): void {
    this.activeMenuUserId = this.activeMenuUserId === userId ? null : userId;
  }

  openEditModal(user: User): void {
    this.selectedUser = user;
    this.editUserForm.patchValue({
      name: user.name,
      username: this.isCurrentUserAdmin ? user.username : '',
      email: this.isCurrentUserAdmin ? user.email : '',
    });
    if (!this.isCurrentUserAdmin) {
      this.editUserForm.get('username')?.clearValidators();
      this.editUserForm.get('username')?.updateValueAndValidity();
      this.editUserForm.get('email')?.clearValidators();
      this.editUserForm.get('email')?.updateValueAndValidity();
    } else {
      this.editUserForm
        .get('username')
        ?.setValidators([Validators.required, Validators.minLength(3)]);
      this.editUserForm.get('username')?.updateValueAndValidity();
      this.editUserForm.get('email')?.setValidators([Validators.required, Validators.email]);
      this.editUserForm.get('email')?.updateValueAndValidity();
    }
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
    this.editUserForm.reset();
  }

  onUpdateUser(): void {

    if (this.editUserForm.invalid || !this.selectedUser) {
      console.error('[UserManagement] Form invalid or no user selected. Aborting.');
      return;
    }


    const formValues = this.editUserForm.value;

    const payload: any = { name: formValues.name };
    if (this.isCurrentUserAdmin) {
      payload.username = formValues.username;
      payload.email = formValues.email;
    }

    this.adminUserService.updateUser(this.selectedUser.id, payload).subscribe({
    
      next: (apiResponse) => { 
        
        const index = this.users.findIndex((u) => u.id === this.selectedUser!.id);
        if (index !== -1) {
          const updatedUsersArray = [...this.users];
          
          updatedUsersArray[index] = { 
            ...updatedUsersArray[index], 
            ...formValues                
          };
          
          this.users = updatedUsersArray;
        } else {
           console.warn('[UserManagement] User to update not found in local array.');
        }
        this.closeModals();
      },
      error: (err) => { 
        console.error('[UserManagement] Update FAILED:', err);
        const errorMessage = err.error?.message || 'Ocurrió un error desconocido.';
        alert(`Error al actualizar: ${errorMessage}`);
      },
    });
  } 

  onChangePassword(): void {
    if (this.passwordForm.invalid || !this.selectedUser) return;
    const { password, password_confirmation } = this.passwordForm.value;
    if (password !== password_confirmation) {
      alert('Las contraseñas no coinciden.');
      return;
    }
    this.adminUserService
      .changeUserPassword(this.selectedUser.id, password, password_confirmation)
      .subscribe({
        next: () => {
          alert('Contraseña actualizada con éxito.');
          this.closeModals();
        },
        error: (err) => alert(`Error al cambiar contraseña: ${err.error?.message || err.message}`),
      });
  }

  onChangeState(user: User, event: Event): void {
    if (!this.isCurrentUserAdmin) return;
    const select = event.target as HTMLSelectElement;
    const stateId = parseInt(select.value, 10);
    const stateName = select.options[select.selectedIndex].text;
    const originalState = user.state; 

    this.adminUserService.updateUser(user.id, { state_id: stateId }).subscribe({
      next: (updatedUser) => { 
        const index = this.users.findIndex(u => u.id === user.id);
        if (index !== -1) {
            const updatedUsersArray = [...this.users];
            updatedUsersArray[index] = { ...updatedUsersArray[index], state: stateName.toLowerCase() };
            this.users = updatedUsersArray; 
        }
      },
      error: (err) => { 
        alert(`Error al cambiar el estado: ${err.error?.message || err.message}`);
        user.state = originalState; 
        select.value =
          this.allStates.find((s) => s.name.toLowerCase() === originalState)?.id.toString() || '';
      },
    });
  }

  onChangeRole(user: User, event: Event): void {
    if (!this.isCurrentUserAdmin) return;
    const select = event.target as HTMLSelectElement;
    const roleId = parseInt(select.value, 10);
    const roleName = select.options[select.selectedIndex].text;
    const originalRole = user.role; 

    this.adminUserService.updateUser(user.id, { role_id: roleId }).subscribe({
      next: (updatedUser) => { 
        const index = this.users.findIndex(u => u.id === user.id);
        if (index !== -1) {
            const updatedUsersArray = [...this.users];
             // Actualiza el rol localmente
            updatedUsersArray[index] = { ...updatedUsersArray[index], role: roleName.toLowerCase() };
            this.users = updatedUsersArray; 
        }
      },
      error: (err) => { 
        alert(`Error al cambiar el rol: ${err.error?.message || err.message}`);
        user.role = originalRole; 
        select.value =
          this.allRoles.find((r) => r.name.toLowerCase() === originalRole)?.id.toString() || '';
      },
    });
  }

  onDeleteAvatar(user: User): void {
    if (confirm(`¿Seguro que quieres eliminar el AVATAR de ${user.username}?`)) {
      this.adminUserService.deleteUserAvatar(user.id).subscribe({
        next: () => alert('Avatar eliminado.'),
        error: (err) => alert(`Error al eliminar avatar: ${err.error?.message || err.message}`),
      });
    }
    this.activeMenuUserId = null;
  }

  onDeleteBanner(user: User): void {
    if (confirm(`¿Seguro que quieres eliminar el BANNER de ${user.username}?`)) {
      this.adminUserService.deleteUserBanner(user.id).subscribe({
        next: () => alert('Banner eliminado.'),
        error: (err) => alert(`Error al eliminar banner: ${err.error?.message || err.message}`),
      });
    }
    this.activeMenuUserId = null;
  }
}