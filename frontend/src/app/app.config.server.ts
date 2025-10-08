/**
 * @file app.config.server.ts
 * @brief Server-specific application configuration.
 * @description This file contains the server-specific application configuration, which is merged with the main application configuration.
 */
import { mergeApplicationConfig, ApplicationConfig } from '@angular/core';
import { provideServerRendering } from '@angular/platform-server';
import { appConfig } from './app.config';

/**
 * @brief Server-specific configuration object.
 * @description This configuration is used when the application is rendered on the server.
 */
const serverConfig: ApplicationConfig = {
  providers: [
    provideServerRendering() // This is the only provider needed for SSR
  ]
};

/**
 * @brief Merged application configuration for the server.
 * @description This exports the merged configuration of the main app and the server-specific configuration.
 */
export const config = mergeApplicationConfig(appConfig, serverConfig);