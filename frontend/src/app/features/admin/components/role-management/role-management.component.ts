import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AdminRoleService, UserRole } from '../../../../core/services/admin-role.service';

@Component({
  selector: 'app-role-management',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './role-management.component.html',
})
export class RoleManagementComponent implements OnInit {
  private roleService = inject(AdminRoleService);
  private fb = inject(FormBuilder);

  public roles: UserRole[] = [];
  public isLoading = true;
  public error: string | null = null;

  public roleForm: FormGroup;
  public isEditMode = false;
  public currentEditingRoleId: number | null = null;

  constructor() {
    this.roleForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(3)]]
    });
  }

  ngOnInit(): void {
    this.loadRoles();
  }

  loadRoles(): void {
    this.isLoading = true;
    this.error = null;
    this.roleService.getRoles().subscribe({
      next: (data) => {
        this.roles = data;
        this.isLoading = false;
      },
      error: (err) => {
        this.error = "No se pudieron cargar los roles.";
        this.isLoading = false;
      }
    });
  }

  onSubmit(): void {
    if (this.roleForm.invalid) {
      return;
    }
    const roleName = this.roleForm.value.name;

    // Lógica para cuando se está EDITANDO un rol
    if (this.isEditMode && this.currentEditingRoleId) {
      this.roleService.updateRole(this.currentEditingRoleId, roleName).subscribe({
        next: () => {
          this.loadRoles(); // Al editar, sí es seguro recargar la lista
          this.resetForm();
        },
        error: (err) => {
          alert('Error al actualizar el rol.');
        }
      });
    }
    // Lógica para cuando se está CREANDO un rol nuevo
    else {
      this.roleService.createRole(roleName).subscribe({
        next: (newRole) => {
          // Tomamos el nuevo rol que devuelve la API y lo añadimos al final de la lista
          this.roles.push(newRole);
          this.resetForm();
        },
        error: (err) => {
          alert('Error al guardar el rol. Es posible que el nombre ya exista.');
        }
      });
    }
  }

  onEdit(role: UserRole): void {
    this.isEditMode = true;
    this.currentEditingRoleId = role.id;
    this.roleForm.setValue({ name: role.name });
  }

  onDelete(role: UserRole): void {
    if (confirm(`¿Estás seguro de que quieres eliminar el rol "${role.name}"?`)) {
      this.roleService.deleteRole(role.id).subscribe({
        next: () => {
          // Para una mejor experiencia, eliminamos el rol de la lista localmente
          this.roles = this.roles.filter(r => r.id !== role.id);
        },
        error: (err) => alert('Error al eliminar el rol.')
      });
    }
  }

  resetForm(): void {
    this.isEditMode = false;
    this.currentEditingRoleId = null;
    this.roleForm.reset();
  }
}