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
    if (/^(https?:)?\/\//i.test(trimmed) || trimmed.startsWith('data:')) {
      return trimmed;
    }
    const withSlash = trimmed.startsWith('/') ? trimmed : `/${trimmed}`;
    const base = environment.baseUrl?.replace(/\/$/, '') || '';
    const url = `${base}${withSlash}`;
    if (cacheBust === 0 || cacheBust) {
      return `${url}?${cacheBust}`;
    }
    return url;
  }
}
