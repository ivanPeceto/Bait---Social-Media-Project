import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { MultimediaContent } from '../models/multimedia-content.model';

interface ApiMultimediaContent {
  id: number;
  url_content?: string;
  type?: string; 
  post_id: number;
  created_at?: string;
  updated_at?: string;
}

@Injectable({ providedIn: 'root' })
export class MultimediaContentService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl;

  uploadToPost(postId: number, file: File): Observable<MultimediaContent> {
    const form = new FormData();
    form.append('file', file, file.name);
    form.append('post_id', String(postId));
    return this.http.post<ApiMultimediaContent>(`${this.apiUrl}/multimedia-contents`, form).pipe(
      map((resp) => {
        
        let rawUrl = (resp.url_content || '').trim();
        let url_content = '';

        if (!rawUrl) {
          url_content = '';
        } else {
          // Limpiar las barras invertidas 
          rawUrl = rawUrl.replace(/\\/g, ''); // Transforma '\/storage\/...' en '/storage/...'

          if (rawUrl.startsWith('multimedia/')) {
            url_content = `/storage/${rawUrl}`;
          } else if (rawUrl.startsWith('uploads/')) {
            url_content = `/${rawUrl}`;
          } else if (rawUrl.startsWith('/')) { 
            url_content = rawUrl; // Ya es correcta (ej: /storage/...)
          } else {
            // Fallback
            url_content = `/${rawUrl}`;
          }
        }
        
        return { id: resp.id, post_id: resp.post_id, url_content } as MultimediaContent;
      })
    );
  }

  delete(multimediaContentId: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/multimedia-contents/${multimediaContentId}`);
  }
}