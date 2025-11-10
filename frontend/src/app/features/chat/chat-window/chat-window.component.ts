// frontend/src/app/features/chat/components/chat-window/chat-window.component.ts
import { Component, EventEmitter, inject, Input, OnDestroy, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { ChatService } from '../../../core/services/chats.service';
import { Chat, Message } from '../../../core/models/chats.model';
import { User } from '../../../core/models/user.model';
import { AuthService } from '../../../core/services/auth.service';
import { EchoService } from '../../../core/services/echo.service';
import { MediaUrlPipe } from '../../../core/pipes/media-url.pipe';
import { PaginatedResponse } from '../../../core/models/api-payloads.model';

@Component({
  selector: 'app-chat-window',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, MediaUrlPipe],
  templateUrl: './chat-window.component.html',
})
export class ChatWindowComponent implements OnInit, OnDestroy {
  private chatService = inject(ChatService);
  private authService = inject(AuthService);
  private echoService = inject(EchoService);
  private fb = inject(FormBuilder);

  @Input() chat!: Chat;
  @Output() close = new EventEmitter<void>();

  public messages: Message[] = [];
  public otherUser: User | undefined;
  public currentUser!: User | null;
  public isLoading = true;
  
  public messageForm = this.fb.group({
    content: ['', [Validators.required, Validators.maxLength(1000)]]
  });

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.otherUser = this.chat.participants.find(u => u.id !== this.currentUser?.id);
    this.loadMessages();
    this.listenForMessages();
  }

  loadMessages(): void {
    this.isLoading = true;
    this.chatService.getChatMessages(this.chat.id).subscribe({
      next: (paginatedResponse: PaginatedResponse<Message>) => {
        this.messages = paginatedResponse.data.reverse();
        this.isLoading = false;
      },
      error: () => this.isLoading = false
    });
  }

  listenForMessages(): void {
    this.echoService.echo?.private(`chat.${this.chat.id}`)
      .listen('.NewMessage', (e: { message: Message }) => {
        this.messages.push(e.message);
      });
  }

  sendMessage(): void {
    if (this.messageForm.invalid) return;
    const content = this.messageForm.value.content!;

    this.messageForm.reset();

    this.chatService.sendMessage(this.chat.id, content).subscribe({
      next: (newMessage) => {
        this.messages.push(newMessage); 
      },
      error: (err) => {
        console.error("Error al enviar mensaje:", err);
      }
    });
  }

  onClose(): void {
    this.close.emit();
  }

  ngOnDestroy(): void {
    this.echoService.echo?.leaveChannel(`chat.${this.chat.id}`);
  }
}