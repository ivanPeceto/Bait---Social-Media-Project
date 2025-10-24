import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router'; // <-- ¡Importamos el módulo de rutas!

@Component({
  selector: 'app-admin-layout',
  standalone: true, // Lo definimos como standalone
  imports: [
    CommonModule,
    RouterModule // <-- ¡Lo añadimos a los imports!
  ],
  templateUrl: './admin-layout.component.html',
})
export class AdminLayoutComponent {

}