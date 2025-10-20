import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AdminStateService, UserState } from '../../services/admin-state.service';

@Component({
  selector: 'app-state-management',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './state-management.component.html',
})
export class StateManagementComponent implements OnInit {
  private stateService = inject(AdminStateService);
  private fb = inject(FormBuilder);

  public states: UserState[] = [];
  public isLoading = true;
  public error: string | null = null;

  public stateForm: FormGroup;
  public isEditMode = false;
  public currentEditingStateId: number | null = null;

  constructor() {
    this.stateForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(3)]]
    });
  }

  ngOnInit(): void {
    this.loadStates();
  }

  loadStates(): void {
    this.isLoading = true;
    this.error = null;
    this.stateService.getStates().subscribe({
      next: (data) => {
        this.states = data;
        this.isLoading = false;
      },
      error: (err) => {
        this.error = "No se pudieron cargar los estados.";
        this.isLoading = false;
      }
    });
  }

  onSubmit(): void {
    if (this.stateForm.invalid) {
      return;
    }
    const stateName = this.stateForm.value.name;

    // Lógica para cuando se está EDITANDO un estado
    if (this.isEditMode && this.currentEditingStateId) {
      this.stateService.updateState(this.currentEditingStateId, stateName).subscribe({
        next: () => {
          this.loadStates(); // Al editar, sí es seguro recargar la lista
          this.resetForm();
        },
        error: (err) => {
          alert('Error al actualizar el estado.');
        }
      });
    }
    // Lógica para cuando se está CREANDO un estado nuevo
    else {
      this.stateService.createState(stateName).subscribe({
        next: (newState) => {
          // Tomamos el nuevo estado que devuelve la API y lo añadimos al final de la lista
          this.states.push(newState);
          this.resetForm();
        },
        error: (err) => {
          alert('Error al guardar el estado. Es posible que el nombre ya exista.');
        }
      });
    }
  }

  onEdit(state: UserState): void {
    this.isEditMode = true;
    this.currentEditingStateId = state.id;
    this.stateForm.setValue({ name: state.name});
  }

  onDelete(state: UserState): void {
    if (confirm(`¿Estás seguro de que quieres eliminar el estado "${state.name}"?`)) {
      this.stateService.deleteState(state.id).subscribe({
        next: () => {
          // Para una mejor experiencia, eliminamos el estado de la lista localmente
          this.states = this.states.filter(s => s.id !== state.id);
        },
        error: (err) => alert('Error al eliminar el estado.')
      });
    }
  }

  resetForm(): void {
    this.isEditMode = false;
    this.currentEditingStateId = null;
    this.stateForm.reset();
  }
}