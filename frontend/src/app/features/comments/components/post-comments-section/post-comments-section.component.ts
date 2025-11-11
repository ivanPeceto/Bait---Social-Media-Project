import { Component, EventEmitter, Input, Output, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Post } from '../../../../core/models/post.model';
import { Comment } from '../../../../core/models/comment.model';
import { CommentItemComponent } from '../comment-item/comment-item.component';
import { CommentFormComponent } from '../comment-form/comment-form.component';
import { CommentService } from '../../../../core/services/comment.service';
import { Observable, of } from 'rxjs';
import { catchError, tap, finalize } from 'rxjs/operators';

@Component({
  selector: 'app-post-comments-section',
  standalone: true,
  imports: [CommonModule, CommentItemComponent, CommentFormComponent],
  templateUrl: './post-comments-section.component.html',
})
export class PostCommentsSectionComponent implements OnInit {
  @Input({ required: true }) post!: Post;
  @Output() commentAdded = new EventEmitter<void>();
  @Output() commentDeleted = new EventEmitter<void>();

  private commentService = inject(CommentService);

  comments$: Observable<Comment[]> = of([]);
  isLoading = false;
  error: string | null = null;
  localComments: Comment[] = []; 

  ngOnInit(): void {
    if (this.post) {
      this.loadComments();
    }
  }

  loadComments(): void {
    this.isLoading = true;
    this.error = null;
    this.localComments = [];

    this.comments$ = this.commentService.getCommentsForPost(this.post.id).pipe(
      tap((comments) => (this.localComments = comments)),
      catchError((err) => {
        console.error('Error loading comments:', err);
        this.error = 'No se pudieron cargar los comentarios.';
        return of([]);
      }),
      finalize(() => {
        this.isLoading = false;
        this.localComments.reverse();
      })
    );
  }

  addComment(content: string): void {
    if (!content || content.trim() === '') {
      alert('El comentario no puede estar vacío.');
      return;
    }

    this.commentService.createComment(this.post.id, content).subscribe({
      next: (newComment) => {
        this.localComments = [...this.localComments, newComment];
        this.comments$ = of(this.localComments);
        this.commentAdded.emit(); // Emitir al padre (home.ts) para actualizar el contador
      },
      error: (err) => {
        alert('Error al enviar el comentario.');
      },
    });
  }

  handleDeleteComment(deleteData: { id: number; asAdmin: boolean }): void {
    const deleteCall = deleteData.asAdmin
      ? this.commentService.deleteCommentAsAdmin(deleteData.id)
      : this.commentService.deleteComment(deleteData.id);

    deleteCall.subscribe({
      next: () => {
        this.localComments = this.localComments.filter((c) => c.id !== deleteData.id);
        this.comments$ = of(this.localComments);
        this.commentDeleted.emit(); // Emitir al padre (home.ts) para actualizar el contador
      },
      error: (err) => {
        alert(`Error al eliminar el comentario: ${err.error?.message || err.message}`);
      },
    });
  }

  handleUpdateComment(updateData: { id: number; content: string }): void {
    this.commentService.updateComment(updateData.id, updateData.content).subscribe({
      next: (updatedComment) => {
        const index = this.localComments.findIndex((c) => c.id === updatedComment.id);
        if (index !== -1) {
          updatedComment.user = this.localComments[index].user; 
          this.localComments[index] = updatedComment;
          this.localComments = [...this.localComments];
          this.comments$ = of(this.localComments);
        }
      },
      error: (err) => {
        alert('Error al actualizar el comentario.');
      },
    });
  }
}