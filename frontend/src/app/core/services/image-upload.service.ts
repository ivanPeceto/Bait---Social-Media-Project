import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Avatar } from '../models/user.model';
import { Banner } from '../models/user.model';

@Injectable({
  providedIn: 'root',
})
export class ImageUploadService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl;

  /**
   * Sube un nuevo archivo de avatar para el usuario autenticado.
   * Llama a POST /api/avatars/upload
   * @param file El archivo de imagen a subir.
   * @returns Observable que emite el objeto Avatar actualizado.
   */
  uploadAvatar(file: File): Observable<Avatar> {
    const formData = new FormData();
    formData.append('avatar', file, file.name); // 'avatar' es el nombre esperado por AvatarUploadRequest.php

    // No establezcas Content-Type manualmente, el navegador lo hará por FormData
    return this.http.post<Avatar>(`${this.apiUrl}/avatars/upload`, formData);
  }

  /**
   * Sube un nuevo archivo de banner para el usuario autenticado.
   * Llama a POST /api/banners/upload
   * @param file El archivo de imagen a subir.
   * @returns Observable que emite el objeto Banner actualizado.
   */
  uploadBanner(file: File): Observable<Banner> {
    const formData = new FormData();
    formData.append('banner', file, file.name); // 'banner' es el nombre esperado por BannerUploadRequest.php

    return this.http.post<Banner>(`${this.apiUrl}/banners/upload`, formData);
  }

   /**
   * Elimina el avatar del usuario autenticado.
   * Llama a DELETE /api/avatars/self
   * @returns Observable (usualmente vacío o con mensaje)
   */
   deleteAvatar(): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/avatars/self`);
  }

  /**
   * Elimina el banner del usuario autenticado.
   * Llama a DELETE /api/banners/self
   * @returns Observable (usualmente vacío o con mensaje)
   */
  deleteBanner(): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/banners/self`);
  }
}