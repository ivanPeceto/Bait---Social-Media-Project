// en src/app/features/admin/admin.module.ts 

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms'; 
import { AdminRoutingModule } from './admin-routing.module';

@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    AdminRoutingModule,
    ReactiveFormsModule 
  ]
})
export class AdminModule { }