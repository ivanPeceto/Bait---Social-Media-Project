// frontend/src/app/features/chat/chat.component.ts
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Chat } from '../../core/models/chats.model';
import { ChatSidebarComponent } from './chat-sidebar/chat-sidebar.component';
import { ChatWindowComponent } from './chat-window/chat-window.component';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, ChatSidebarComponent, ChatWindowComponent],
  template: `
    <app-chat-sidebar (chatSelected)="onChatSelected($event)"></app-chat-sidebar>
    
    <app-chat-window
      *ngIf="activeChat"
      [chat]="activeChat"
      (close)="onChatClosed()">
    </app-chat-window>
  `
})
export class ChatComponent {
  
  public activeChat: Chat | null = null;

  onChatSelected(chat: Chat): void {
    this.activeChat = chat;
  }

  onChatClosed(): void {
    this.activeChat = null;
  }
}