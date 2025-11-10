import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';
import { Message, Chat } from '../models/';
import { PaginatedResponse } from '../models/api-payloads.model';

@Injectable({
  providedIn: 'root'
})
export class ChatService {

  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}/chats`; 

  /**
   * Obtiene todos los chats del usuario autenticado.
   * Corresponde a: ChatController::index
   * GET /api/chats
   */
  getAllChats(): Observable<Chat[]> {
    return this.http.get<{ data: Chat[] }>(this.apiUrl).pipe(
      map(response => response.data)
    );
  }

  /**
   * Obtiene un chat específico por su ID.
   * Corresponde a: ChatController::show
   * GET /api/chats/{chatId}
   */
  getChatById(chatId: number): Observable<Chat> {
    return this.http.get<{ data: Chat }>(`${this.apiUrl}/${chatId}`).pipe(
      map(response => response.data)
    );
  }

  /**
   * Crea un nuevo chat con un conjunto de participantes.
   * Corresponde a: ChatController::store
   * POST /api/chats
   */
  createChat(participantIds: number[]): Observable<Chat> {
    const payload = { participants: participantIds };
    return this.http.post<{ data: Chat }>(this.apiUrl, payload).pipe(
      map(response => response.data)
    );
  }

  /**
   * Obtiene los mensajes de un chat específico, de forma paginada.
   * Corresponde a: MessageController::index
   * GET /api/chats/{chatId}/messages
   */
  getChatMessages(chatId: number, page: number = 1): Observable<PaginatedResponse<Message>> {
    const params = new HttpParams().set('page', page.toString());
    
    return this.http.get<PaginatedResponse<Message>>(
      `${this.apiUrl}/${chatId}/messages`, 
      { params }
    );
  }

  /**
   * Envía un nuevo mensaje a un chat.
   * Corresponde a: MessageController::store
   * POST /api/chats/{chatId}/messages
   */
  sendMessage(chatId: number, content: string): Observable<Message> {
    const payload = { content_messages: content };
    
    return this.http.post<{ data: Message }>(
      `${this.apiUrl}/${chatId}/messages`, 
      payload
    ).pipe(
      map(response => response.data)
    );
  }
}