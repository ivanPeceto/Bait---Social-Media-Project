import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Observable, of } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { ChatService } from '../../../core/services/chats.service';
import { Chat } from '../../../core/models/chats.model';
import { User } from '../../../core/models/user.model';
import { AuthService } from '../../../core/services/auth.service';
import { MediaUrlPipe } from '../../../core/pipes/media-url.pipe';

@Component({
  selector: 'app-chat-sidebar',
  standalone: true,
  imports: [CommonModule, MediaUrlPipe],
  templateUrl: './chat-sidebar.component.html',
})
export class ChatSidebarComponent implements OnInit {
  private chatService = inject(ChatService);
  private authService = inject(AuthService);

  public chats$!: Observable<Chat[]>;
  private currentUser!: User | null;
  public chattableUsers$!: Observable<User[]>;

  @Output() chatSelected = new EventEmitter<Chat>();

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadChats();
    this.loadChattableUsers();
  }

  loadChats(): void {
    this.chats$ = this.chatService.getAllChats();
  }

  loadChattableUsers(): void {
    this.chattableUsers$ = this.chatService.getChattableUsers();
  }

  getOtherUser(chat: Chat): User | undefined {
    return chat.participants.find(u => u.id !== this.currentUser?.id);
  }

  onSelectChat(chat: Chat): void {
    this.chatSelected.emit(chat);
  }

  onSelectUser(user: User): void {
    this.chatService.createChat([user.id]).pipe(
      catchError((error: any) => {
        // Si el chat ya existe
        if (error.status === 409) {
          return this.chatService.getAllChats().pipe(
            map((chats: Chat[]) => chats.find((chat: Chat) => chat.participants.some(u => u.id === user.id))),
            tap((existingChat: Chat | undefined) => {
              if (existingChat) {
                this.onSelectChat(existingChat);
              }
            })
          );
        }
        // Para otros errores, solo loguea
        console.error("Error al crear el chat:", error);
        return of(null);
      })
    ).subscribe((newChat: Chat | null | undefined) => {
      if (newChat) {
        this.loadChats();
        this.onSelectChat(newChat);
      }
    });
  }

  trackByChatId(index: number, chat: Chat): number {
    return chat.id;
  }

  trackByUserId(index: number, user: User): number {
    return user.id;
  }
}