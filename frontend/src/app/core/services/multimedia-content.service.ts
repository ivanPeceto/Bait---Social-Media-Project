import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { MultimediaContent } from '../models/multimedia-content.model';

interface ApiMultimediaContent {
  id: number;
  url_multimedia_contents?: string;
  url?: string;
  type_multimedia_contents?: string;
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
        const rawUrl = (resp.url_multimedia_contents || resp.url || '').trim();
        let url_content = rawUrl;
        if (!rawUrl) {
          url_content = '';
        } else if (rawUrl.startsWith('multimedia/')) {
          url_content = `/storage/${rawUrl}`;
        } else if (rawUrl.startsWith('uploads/')) {
          url_content = `/${rawUrl}`;
        } else if (!rawUrl.startsWith('/')) {
          url_content = `/${rawUrl}`;
        }
        return { id: resp.id, post_id: resp.post_id, url_content } as MultimediaContent;
      })
    );
  }

  delete(multimediaContentId: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/multimedia-contents/${multimediaContentId}`);
  }
}
