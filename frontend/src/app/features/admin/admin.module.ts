// en src/app/features/admin/admin.module.ts (CORREGIDO)

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms'; // ReactiveFormsModule puede ser útil si agregas formularios aquí
import { AdminRoutingModule } from './admin-routing.module';

@NgModule({
  // 'declarations' está vacío, lo cual es correcto porque tus componentes son standalone.
  declarations: [],
  // 'imports' solo debe contener otros MÓDULOS.
  imports: [
    CommonModule,
    AdminRoutingModule,
    ReactiveFormsModule 
  ]
})
export class AdminModule { }