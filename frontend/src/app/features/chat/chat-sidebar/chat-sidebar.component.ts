import { Component, EventEmitter, inject, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Observable } from 'rxjs';
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

  @Output() chatSelected = new EventEmitter<Chat>();

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadChats();
  }

  loadChats(): void {
    this.chats$ = this.chatService.getAllChats();
  }

  getOtherUser(chat: Chat): User | undefined {
    return chat.users.find(u => u.id !== this.currentUser?.id);
  }

  onSelectChat(chat: Chat): void {
    this.chatSelected.emit(chat);
  }

  trackByChatId(index: number, chat: Chat): number {
    return chat.id;
  }
}