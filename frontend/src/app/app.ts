/**
 * @file app.ts
 * @brief The root component of the application.
 */
import { Component, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  /**
   * @brief The title of the application.
   */
  protected readonly title = signal('frontend');
}