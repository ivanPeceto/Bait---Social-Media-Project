/**
 * @file login.component.ts
 * @brief Logic for the LoginComponent.
 */
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { AuthService } from '../services/auth.service';

/**
 * @class LoginComponent
 * @brief Manages the user login view and interactions.
 * @description This component displays the login form and will handle user input
 * and authentication logic.
 */

@Component({
  selector: 'app-login',
  templateUrl: './login.html',
  styleUrl: './login.scss' 
})
export class Login {
  /**
   * @brief The constructor for the LoginComponent.
   */
  constructor() {}
}