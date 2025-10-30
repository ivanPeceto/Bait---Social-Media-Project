/**
 * @file app.routes.server.ts
 * @brief Defines the server-side routes for the application.
 * @description This file configures the server-side rendering mode for the application routes.
 */
import { RenderMode, ServerRoute } from '@angular/ssr';

/**
 * @brief An array of server-side route configurations.
 * @description This configuration ensures that all routes are pre-rendered on the server.
 */
export const serverRoutes: ServerRoute[] = [
  {
    path: '**',
    renderMode: RenderMode.Prerender
  }
];