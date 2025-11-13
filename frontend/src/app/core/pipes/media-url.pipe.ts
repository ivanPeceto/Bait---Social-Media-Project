import { Pipe, PipeTransform } from '@angular/core';
import { environment } from '../../../environments/environment';

@Pipe({
  name: 'mediaUrl',
  standalone: true,
})
export class MediaUrlPipe implements PipeTransform {
  transform(path?: string | null, cacheBust?: number | string | null): string {
    if (!path) return '';
    
    const trimmed = String(path).trim();
    
    if (trimmed.startsWith('data:')) {
      return trimmed;
    }

    let finalUrl: string;
    
    if (/^(https?:)?\/\//i.test(trimmed)) {
      // Ya es una URL absoluta
      finalUrl = trimmed;
    } else {
      // Es una URL relativa, construirla
      const withSlash = trimmed.startsWith('/') ? trimmed : `/${trimmed}`;
      const base = environment.baseUrl?.replace(/\/$/, '') || '';
      finalUrl = `${base}${withSlash}`;
    }

    if (cacheBust === 0 || cacheBust) {
      // Añade '?' o '&' dependiendo de si ya hay parámetros
      const separator = finalUrl.includes('?') ? '&' : '?';
      return `${finalUrl}${separator}${cacheBust}`;
    }

    return finalUrl;
  }
}
