import { Component, EventEmitter, Input, Output, inject, OnInit, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Post } from '../../../../core/models/post.model';
import { Comment } from '../../../../core/models/comment.model'; 
import { CommentItemComponent } from '../comment-item/comment-item.component';
import { CommentFormComponent } from '../comment-form/comment-form.component';
import { CommentService } from '../../services/comment.service';
import { Observable, of } from 'rxjs';
import { environment } from '../../../../../environments/environment';
import { catchError, tap, finalize } from 'rxjs/operators';

@Component({
  selector: 'app-post-comments-modal',
  standalone: true,
  imports: [CommonModule, CommentItemComponent, CommentFormComponent],
  templateUrl: './post-comments-modal.component.html',
})
export class PostCommentsModalComponent implements OnInit, OnChanges {
  @Input({ required: true }) post!: Post;
  @Input() isOpen: boolean = false;
  @Output() closeModal = new EventEmitter<void>();

  private commentService = inject(CommentService);

  comments$: Observable<Comment[]> = of([]);
  isLoading = false;
  error: string | null = null;
  localComments: Comment[] = [];
  baseUrl = environment.apiUrl.replace('/api', '');

  ngOnInit(): void {
    if (this.post) {
        this.loadComments();
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['post'] && !changes['post'].firstChange && this.post) {
        this.loadComments();
    }
  }

  loadComments(): void {
    this.isLoading = true;
    this.error = null;
    this.localComments = [];
    this.comments$ = this.commentService.getCommentsForPost(this.post.id).pipe(
      tap(comments => this.localComments = comments),
      catchError(err => {
        console.error("Error loading comments:", err);
        this.error = "No se pudieron cargar los comentarios.";
        return of([]);
      }),
      finalize(() => this.isLoading = false)
    );
  }

  addComment(content: string): void {

    if (!content || content.trim() === '') {
        alert('El comentario no puede estar vacío.'); 
        return; 
    }


    this.commentService.createComment(this.post.id, content).subscribe({
      next: (newComment) => {
        this.localComments = [newComment, ...this.localComments];
        this.comments$ = of(this.localComments);
      },
      error: (err) => {
        alert("Error al enviar el comentario.");
      }
    });
  }

  /**
   * Maneja el evento emitido por CommentItemComponent para eliminar un comentario.
   */
  handleDeleteComment(commentId: number): void {
    this.commentService.deleteComment(commentId).subscribe({
      next: () => {
        this.localComments = this.localComments.filter(c => c.id !== commentId);
        this.comments$ = of(this.localComments);
       
      },
      error: (err) => {
        alert("Error al eliminar el comentario.");
      }
    });
  }

  /**
   * Maneja el evento emitido por CommentItemComponent para actualizar un comentario.
   */
  handleUpdateComment(updateData: {id: number, content: string}): void {
    this.commentService.updateComment(updateData.id, updateData.content).subscribe({
      next: (updatedComment) => {
        const index = this.localComments.findIndex(c => c.id === updatedComment.id);
        if (index !== -1) {
          this.localComments[index] = updatedComment;
          this.localComments = [...this.localComments]; 
          this.comments$ = of(this.localComments);
        }
      },
      error: (err) => {
        console.error("Error updating comment:", err);
        alert("Error al actualizar el comentario.");
      }
    });
  }

  close(): void {
    this.closeModal.emit();
  }
}