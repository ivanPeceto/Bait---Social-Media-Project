import {
  Component,
  EventEmitter,
  Input,
  Output,
  inject,
  OnInit,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { Post } from '../../../../core/models/post.model';
import { Comment } from '../../../../core/models/comment.model';
import { CommentItemComponent } from '../comment-item/comment-item.component';
import { CommentFormComponent } from '../comment-form/comment-form.component';
import { CommentService } from '../../../../core/services/comment.service';
import { Observable, of } from 'rxjs';
import { environment } from '../../../../../environments/environment';
// Importamos solo lo que necesitamos (¡ya no 'map'!)
import { catchError, tap, finalize } from 'rxjs/operators';
// Ya no necesitamos AuthService ni User
// import { AuthService } from '../../../../core/services/auth.service';
// import { User } from '../../../../core/models/user.model';

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
  @Output() commentAdded = new EventEmitter<void>();
  @Output() commentDeleted = new EventEmitter<void>();

  private commentService = inject(CommentService);
  // private authService = inject(AuthService); // <-- ELIMINADO

  comments$: Observable<Comment[]> = of([]);
  isLoading = false;
  error: string | null = null;
  localComments: Comment[] = [];
  // currentUser: User | null = null; // <-- ELIMINADO
  baseUrl = environment.apiUrl.replace('/api', '');

  ngOnInit(): void {
    // this.currentUser = this.authService.getCurrentUser(); // <-- ELIMINADO
    if (this.post) {
      this.loadComments();
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['post'] && !changes['post'].firstChange && this.post) {
      this.loadComments();
    }
  }

  // --- CÓDIGO LIMPIO (SIN PARCHE) ---
  loadComments(): void {
    this.isLoading = true;
    this.error = null;
    this.localComments = [];

    this.comments$ = this.commentService.getCommentsForPost(this.post.id).pipe(
      // Ya no se necesita el .map()
      tap((comments) => (this.localComments = comments)), // Carga directa
      catchError((err) => {
        console.error('Error loading comments:', err);
        this.error = 'No se pudieron cargar los comentarios.';
        return of([]);
      }),
      finalize(() => (this.isLoading = false))
    );
  }

  // --- CÓDIGO LIMPIO (SIN PARCHE) ---
  addComment(content: string): void {
    if (!content || content.trim() === '') {
      alert('El comentario no puede estar vacío.');
      return;
    }

    this.commentService.createComment(this.post.id, content).subscribe({
      next: (newComment) => {
        // newComment ahora viene COMPLETO desde la API (con avatar)
        
        // Ya no necesitamos "hidratar"
        this.localComments = [newComment, ...this.localComments];
        this.comments$ = of(this.localComments);
        this.commentAdded.emit();
      },
      error: (err) => {
        alert('Error al enviar el comentario.');
      },
    });
  }

  // ===== INICIO: MÉTODO handleDeleteComment MODIFICADO =====
  /**
   * Maneja el evento emitido por CommentItemComponent para eliminar un comentario.
   */
  handleDeleteComment(deleteData: { id: number; asAdmin: boolean }): void {
    // Recibe el objeto
    // Determina qué método del servicio llamar
    const deleteCall = deleteData.asAdmin
      ? this.commentService.deleteCommentAsAdmin(deleteData.id)
      : this.commentService.deleteComment(deleteData.id); // Método normal para el dueño

    deleteCall.subscribe({
      next: () => {
        this.localComments = this.localComments.filter((c) => c.id !== deleteData.id);
        this.comments$ = of(this.localComments);
        console.log('Comentario eliminado con éxito'); // Mejor log
        this.commentDeleted.emit();
      },
      error: (err) => {
        console.error('Error deleting comment:', err); // Mejor log
        alert(`Error al eliminar el comentario: ${err.error?.message || err.message}`);
      },
    });
  }

  /**
   * Maneja el evento emitido por CommentItemComponent para actualizar un comentario.
   */
  handleUpdateComment(updateData: { id: number; content: string }): void {
    this.commentService.updateComment(updateData.id, updateData.content).subscribe({
      next: (updatedComment) => {
        const index = this.localComments.findIndex((c) => c.id === updatedComment.id);
        if (index !== -1) {
          // Aseguramos que el usuario del comentario actualizado se mantenga
          // ya que el 'update' podría no devolver el 'user' completo
          updatedComment.user = this.localComments[index].user; 
          this.localComments[index] = updatedComment;
          this.localComments = [...this.localComments];
          this.comments$ = of(this.localComments);
        }
      },
      error: (err) => {
        console.error('Error updating comment:', err);
        alert('Error al actualizar el comentario.');
      },
    });
  }

  close(): void {
    this.closeModal.emit();
  }
}