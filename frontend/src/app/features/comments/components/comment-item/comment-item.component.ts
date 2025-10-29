import { Component, Input, Output, EventEmitter, inject, OnInit } from '@angular/core'; 
import { CommonModule, DatePipe } from '@angular/common';
import { Comment } from '../../../../core/models/comment.model'; 
import { RouterLink } from '@angular/router';
import { environment } from '../../../../../environments/environment'; 
import { AuthService } from '../../../../core/services/auth.service'; 
import { User } from '../../../../core/models/user.model'; 
import { CommentFormComponent } from '../comment-form/comment-form.component'; 

@Component({
  selector: 'app-comment-item',
  standalone: true,
  imports: [CommonModule, RouterLink, DatePipe, CommentFormComponent], 
  templateUrl: './comment-item.component.html',
})
export class CommentItemComponent implements OnInit { 
  @Input({ required: true }) comment!: Comment;
  @Output() commentDeleted = new EventEmitter<{id: number, asAdmin: boolean}>(); // Envía objeto
  @Output() commentUpdated = new EventEmitter<{id: number, content: string}>();

  private authService = inject(AuthService); 
  
  currentUser: User | null = null; 
  isOwner: boolean = false;        
  isEditing: boolean = false;      
  isAdminOrMod: boolean = false;
  
  baseUrl = environment.apiUrl.replace('/api', ''); 

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.isOwner = !!this.currentUser && this.currentUser.id === this.comment.user.id;
    this.isAdminOrMod = !!this.currentUser &&
                       (this.currentUser.role?.toLowerCase() === 'admin' ||
                        this.currentUser.role?.toLowerCase() === 'moderator');
  }

  
  startEditing(): void {
    this.isEditing = true;
  }

  cancelEditing(): void {
    this.isEditing = false;
  }

  saveEdit(newContent: string): void {
    if (newContent && newContent.trim() !== this.comment.content) {
      this.commentUpdated.emit({ id: this.comment.id, content: newContent });
    }
    this.isEditing = false; 
  }

  deleteComment(): void {
    if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
      this.commentDeleted.emit({
        id: this.comment.id,
        asAdmin: this.isAdminOrMod && !this.isOwner
      });
    }
  }
}