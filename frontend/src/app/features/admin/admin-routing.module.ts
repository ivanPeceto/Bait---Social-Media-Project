import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminLayoutComponent } from './components/admin-layout/admin-layout.component';
import { UserManagementComponent } from './components/user-management/user-management.component';
import { RoleManagementComponent } from './components/role-management/role-management.component';
import { StateManagementComponent } from './components/state-management/state-management.component';

const routes: Routes = [
  {
    path: '',
    component: AdminLayoutComponent,
    children: [
      { path: 'users', component: UserManagementComponent },
      { path: 'roles', component: RoleManagementComponent }, 
      { path: 'states', component: StateManagementComponent }, 
      
      { path: '', redirectTo: 'users', pathMatch: 'full' }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }