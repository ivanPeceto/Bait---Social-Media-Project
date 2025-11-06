import { Component, inject, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  Validators,
  ReactiveFormsModule,
  FormControl,
} from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { Observable, of, forkJoin } from 'rxjs';
import {
  debounceTime,
  distinctUntilChanged,
  switchMap,
  tap,
  map,
  take,
  catchError,
} from 'rxjs/operators';
import Echo from 'laravel-echo';
import { AuthService } from '../../core/services/auth.service';
import { HttpErrorResponse } from '@angular/common/http';
import { PostService } from '../../core/services/post.service';
import { Post } from '../../core/models/post.model';
import { SearchService, UserSearchResult } from '../../core/services/search.service';
import { InteractionService } from '../../core/services/interaction.service';
import { CreateReactionPayload, CreateRepostPayload } from '../../core/models/api-payloads.model';
import { UserReactionStatus } from '../../core/models/user-reaction-status.model';
import { environment } from '../../../environments/environment';
import { MediaUrlPipe } from '../../core/pipes/media-url.pipe';
import { MultimediaContent } from '../../core/models/multimedia-content.model';
import { MultimediaContentService } from '../../core/services/multimedia-content.service';
import { PostCommentsModalComponent } from '../comments/components/post-comments-modal/post-comments-modal.component';
import { EchoService } from '../../core/services/echo.service';
import { User } from '../../core/models/user.model';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    DatePipe,
    RouterLink,
    MediaUrlPipe,
    PostCommentsModalComponent,
  ],
  templateUrl: './home.html',
})
export default class Home implements OnInit, OnDestroy {

  private interactionService = inject(InteractionService);
  private authService = inject(AuthService);
  private searchService = inject(SearchService);
  private router = inject(Router);
  private multimediaService = inject(MultimediaContentService);
  private echoService = inject(EchoService);

  private readonly LIKE_REACTION_TYPE_ID = 1;
  public readonly apiUrlForImages = environment.baseUrl;
  searchControl = new FormControl('');
  searchResults: any[] = [];
  isSearching = false;
  showResults = false;
  public currentUser: any;
  public posts: Post[] = [];
  public postForm: FormGroup;
  public openPostId: number | null = null;
  public editingPostId: number | null = null;
  public editContent: string = '';
  public apiErrors: any = null;
  public echo: Echo<'pusher'> | null = null;

  public isLoading = true;
  public cacheBustTs = Date.now();
  public selectedFile: File | null = null;
  public previewUrl: string | null = null;
  public isUploadingMedia = false;
  public isCommentsModalOpen = false;
  public selectedPostForComments: Post | null = null;

  constructor(private fb: FormBuilder, private postService: PostService) {
    this.postForm = this.fb.group({
      content_posts: ['', [Validators.required, Validators.maxLength(280)]],
    });
  }

  onSelectImage(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] || null;
    if (!file) return;
    if (!file.type.startsWith('image/')) {
      alert('Selecciona un archivo de imagen válido.');
      input.value = '';
      return;
    }
    this.selectedFile = file;
    const reader = new FileReader();
    reader.onload = () => {
      this.previewUrl = reader.result as string;
    };
    reader.readAsDataURL(file);
    input.value = '';
  }

  onRemoveSelectedImage(): void {
    this.selectedFile = null;
    this.previewUrl = null;
  }

  deletePostImage(post: Post, media: MultimediaContent): void {
    if (!this.currentUser || this.currentUser.id !== post.user_id) return;
    if (!confirm('¿Eliminar imagen de este post?')) return;
    this.multimediaService.delete(media.id).subscribe({
      next: () => {
        post.multimedia_contents = (post.multimedia_contents || []).filter(
          (m) => m.id !== media.id
        );
      },
      error: (err) => {
        console.error('Error al eliminar imagen del post:', err);
        alert('No se pudo eliminar la imagen.');
      },
    });
  }

  startEditPost(post: Post): void {
    if (!post) return;
    this.editingPostId = post.id;
    this.editContent = post.content_posts;
    this.openPostId = null;
  }

  cancelEditPost(): void {
    this.editingPostId = null;
    this.editContent = '';
  }

  saveEditPost(post: Post): void {
    if (!this.editingPostId || this.editingPostId !== post.id) return;
    const content = (this.editContent || '').trim();
    if (content.length === 0 || content.length > 280) return;
    this.postService.updatePost(post.id, content).subscribe({
      next: (updated: Post) => {
        this.posts = this.posts.map((p) =>
          p.id === post.id
            ? ({
                ...p,
                content_posts: updated.content_posts,
                updated_at: updated.updated_at,
              } as any)
            : p
        );
        this.editingPostId = null;
        this.editContent = '';
      },
      error: (err) => {
        console.error('Error al actualizar el post:', err);
        alert('No se pudo actualizar el post.');
      },
    });
  }

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.authService.currentUserChanges$.subscribe((user) => {
      this.currentUser = user;
      this.cacheBustTs = Date.now();
      if (user && this.posts && this.posts.length > 0) {
        this.posts = this.posts.map((p) => {
          if (p.user_id === user.id) {
            return {
              ...p,
              user: user,
            } as any;
          }
          return p;
        });
      }
    });
    this.loadPosts();
    this.setupSearch();
    this.setupWebSocketListeners();
  }

  /**
   * Limpia los listeners de WebSocket al destruir el componente.
   */
  ngOnDestroy(): void {
    const echo = this.echoService.echo;
    if (echo) {
      // Salir del canal privado
      if (this.currentUser) {
        echo.leave(`App.Models.User.${this.currentUser.id}`);
      }
      // Salir de todos los canales de posts
      this.posts.forEach(post => {
        echo.leaveChannel(`post.${post.id}`);
      });
    }
  }

  /**
   * Configures ws listeners for posts feed.
  */
  private setupWebSocketListeners(): void {
    if (!this.currentUser) return;
    
    const echo = this.echoService.echo;
    if (!echo) {
      console.warn('Echo instance not available. Real-time updates disabled.');
      return; 
    }

    echo.private(`App.Models.User.${this.currentUser.id}`)
      .listen('.NewPost', (data: { post: Post }) => {
        if (data.post && !this.posts.find(p => p.id === data.post.id)) {
          // Sets a timeout to mmake sure that Angular sees the changes.
          setTimeout(() => {
            this.posts = [data.post, ...this.posts];
          }, 100);
        }
      });
  }

  private setupSearch(): void {
    this.searchControl.valueChanges
      .pipe(
        debounceTime(300),
        distinctUntilChanged(),
        switchMap((query) => {
          if (!query || query.trim() === '') {
            this.searchResults = [];
            this.showResults = false;
            return [];
          }
          this.isSearching = true;
          this.showResults = true;
          const q = (query || '').toString();
          if (q.startsWith('@')) {
            const username = q.slice(1).trim();
            if (!username) {
              this.isSearching = false;
              this.searchResults = [];
              return [];
            }
            return forkJoin([
              this.searchService.searchByUsername(username).pipe(catchError(() => of([]))),
              this.searchService.searchByName(username).pipe(catchError(() => of([]))),
            ]).pipe(
              map(([byUsername, byName]) => {
                const seen = new Set<number>();
                const merged = [] as any[];
                for (const u of byUsername || []) {
                  if (!seen.has(u.id)) {
                    seen.add(u.id);
                    merged.push(u);
                  }
                }
                for (const u of byName || []) {
                  if (!seen.has(u.id)) {
                    seen.add(u.id);
                    merged.push(u);
                  }
                }
                return merged;
              })
            );
          }
          return this.searchService.searchByName(q);
        })
      )
      .subscribe({
        next: (results: any) => {
          this.searchResults = results || [];
          this.isSearching = false;
        },
        error: (error) => {
          console.error('Error searching users:', error);
          this.isSearching = false;
          this.searchResults = [];
        },
      });
  }

  clearSearch(): void {
    this.searchControl.setValue('');
    this.showResults = false;
    this.searchResults = [];
  }

  goToProfile(username: string): void {
    this.router.navigate(['/profile', username]);
    this.clearSearch();
  }

  onPostSubmit(): void {
    if (this.postForm.invalid) {
      return;
    }

    this.apiErrors = null;
    const content = this.postForm.value.content_posts?.trim();
    if (!content) {
      return;
    }

    // --- [INICIO DE LA SOLUCIÓN DEFINITIVA] ---
    // 1. Obtenemos el usuario MÁS FRESCO directamente del observable.
    //    'take(1)' obtiene el valor actual y se desuscribe automáticamente.
    this.authService.currentUserChanges$.pipe(take(1)).subscribe((freshUser) => {
      // 2. AHORA que tenemos el 'freshUser', llamamos al servicio para crear el post.
      this.postService.createPost(content).subscribe({
        next: (createdPost: Post) => {
          // 3. Hidratamos el post usando el 'freshUser' que obtuvimos en el paso 1.
          const hydratedPost: Post = {
            ...createdPost,
            user: freshUser || createdPost.user,
            user_id: freshUser?.id || createdPost.user_id,
            multimedia_contents: createdPost.multimedia_contents || [],
          } as any;

          // 4. El resto de tu lógica para subir la imagen (esto ya está bien)
          if (this.selectedFile) {
            this.isUploadingMedia = true;
            this.multimediaService.uploadToPost(createdPost.id, this.selectedFile).subscribe({
              next: (media: MultimediaContent) => {
                hydratedPost.multimedia_contents = [media];
                this.posts = [hydratedPost, ...this.posts];
                this.isUploadingMedia = false;
                this.onRemoveSelectedImage();
                this.postForm.reset();
                this.cacheBustTs = Date.now();
              },
              error: (err) => {
                console.error('Error al subir imagen del post:', err);
                this.posts = [hydratedPost, ...this.posts];
                this.isUploadingMedia = false;
                this.onRemoveSelectedImage();
                this.postForm.reset();
                alert('El post se creó, pero la imagen no pudo subirse.');
                this.cacheBustTs = Date.now(); // Asegúrate de refrescar aquí también
              },
            });
          } else {
            this.posts = [hydratedPost, ...this.posts];
            this.postForm.reset();
            this.cacheBustTs = Date.now();
          }
        },
        error: (err: HttpErrorResponse) => {
          if (err?.error) {
            this.apiErrors = err.error;
          } else {
            this.apiErrors = { general: ['Ocurrió un error al crear el post.'] };
          }
          console.error('Error al crear el post:', err);
        },
      });
    });
    // --- [FIN DE LA SOLUCIÓN DEFINITIVA] ---
  }

  /**
   * Carga la lista inicial de posts y luego verifica el estado de 'like'
   * del usuario actual para cada post mediante llamadas adicionales.
   */
  loadPosts(): void {
    this.postService
      .getPosts()
      .pipe(
        switchMap((initialPosts: Post[] | any) => {
          const postsArray = Array.isArray(initialPosts?.data)
            ? initialPosts.data
            : Array.isArray(initialPosts)
            ? initialPosts
            : [];
          if (postsArray.length === 0) return of([]);

          const reactionChecks$: Observable<UserReactionStatus>[] = postsArray.map((post: Post) =>
            this.interactionService
              .checkUserReaction(post.id)
              .pipe(catchError(() => of({ has_reacted: false, reaction_type_id: null })))
          );
          return forkJoin(reactionChecks$).pipe(
            map((reactionStatuses: UserReactionStatus[]) =>
              postsArray.map((post: Post, index: number) => {
                post.is_liked_by_user =
                  reactionStatuses[index].has_reacted &&
                  reactionStatuses[index].reaction_type_id === this.LIKE_REACTION_TYPE_ID;
                return post;
              })
            )
          );
        })
      )
      .subscribe({
      next: (postsWithLikeStatus: Post[]) => {
        this.posts = postsWithLikeStatus;
        this.isLoading = false;
        this.listenToPostUpdates();
      },
      error: (err) => {
        console.error('Error al cargar posts o verificar likes:', err);
        this.isLoading = false;
      },
    });
  }

  /**
   * Se suscribe a los canales públicos de CADA post visible para
   * escuchar actualizaciones de likes y reposts.
   */
  private listenToPostUpdates(): void {
    const echo = this.echoService.echo;
    if (!echo) return;

    // Desuscribirse de listeners antiguos si existieran
    this.posts.forEach(post => {
      echo.leaveChannel(`post.${post.id}`);
    });

    // Suscribirse a los nuevos
    this.posts.forEach(post => {
      echo.channel(`post.${post.id}`)
        .listen('.PostReactionUpdated', (data: { post: Post }) => {
          this.updatePostInArray(data.post);
        })
        .listen('.PostRepostUpdated', (data: { post: Post }) => {
          this.updatePostInArray(data.post);
        });
    });
  }

 /**
   * Helper para actualizar un post en el array 'this.posts' de forma inmutable.
   */
  private updatePostInArray(updatedPost: Post): void {
    const index = this.posts.findIndex(p => p.id === updatedPost.id);
    if (index > -1) {
      // Preservamos el estado 'is_liked_by_user' si la actualización no lo trae
      // (aunque debería, si el backend usa el Resource)
      const originalPost = this.posts[index];
      this.posts[index] = { 
        ...originalPost, 
        ...updatedPost,
        // Aseguramos que el estado optimista del like no se pise
        is_liked_by_user: updatedPost.is_liked_by_user ?? originalPost.is_liked_by_user,
        user: originalPost.user // Mantenemos el objeto 'user' original
      };
    }
  }

  /**
   * Gestiona el click en el botón de 'Like'. Actualiza la UI de forma optimista
   * y luego llama al servicio para crear/eliminar la reacción en el backend.
   * @param post El objeto Post al que se le dio like/unlike.
   */
  onToggleLike(post: Post): void {
    const previousState = {
      is_liked_by_user: post.is_liked_by_user,
      reactions_count: post.reactions_count || 0,
    };

    if (post.is_liked_by_user) {
      post.reactions_count = (post.reactions_count || 1) - 1;
      post.is_liked_by_user = false;
    } else {
      post.reactions_count = (post.reactions_count || 0) + 1;
      post.is_liked_by_user = true;
    }

    const payload: CreateReactionPayload = {
      post_id: post.id,
      reaction_type_id: this.LIKE_REACTION_TYPE_ID,
    };
    this.interactionService.toggleReaction(payload).subscribe({
      next: (response) => {},
      error: (err) => {
        console.error('Error al reaccionar al post:', err);
        post.is_liked_by_user = previousState.is_liked_by_user;
        post.reactions_count = previousState.reactions_count;
      },
    });
  }

  togglePostMenu(postId: number): void {
    this.openPostId = this.openPostId === postId ? null : postId;
  }

  onDeletePost(post: Post): void {
    this.openPostId = null;
    if (!post) return;
    if (confirm('¿Estás seguro de que quieres eliminar esta publicación?')) {
      const isOwner = this.currentUser && this.currentUser.id === post.user_id;
      const role = (this.currentUser?.role || '').toLowerCase();
      const isAdminOrMod = role === 'admin' || role === 'moderator';

      const request$ = isOwner
        ? this.postService.deletePost(post.id)
        : isAdminOrMod
        ? this.postService.deletePostPrivileged(post.id)
        : null;

      if (!request$) return; // No permitido

      request$.subscribe({
        next: () => {
          this.posts = this.posts.filter((p) => p.id !== post.id);
        },
        error: (err) => {
          console.error('Error al eliminar el post', err);
        },
      });
    }
  }

  onToggleRepost(post: Post): void {
    const previousRepostCount = post.reposts_count || 0;
    const payload: CreateRepostPayload = { post_id: post.id };

    this.interactionService.toggleRepost(payload).subscribe({
      next: (updatedPost) => {
        // --- [FIX CONTADOR] ---
        // 1. Actualizar el contador con la respuesta de la API
        post.reposts_count = updatedPost.reposts_count;

        // 2. Forzar la detección de cambios de Angular
        const index = this.posts.findIndex((p) => p.id === post.id);
        if (index !== -1) {
          this.posts[index] = { ...post }; // Reemplazar objeto
          this.posts = [...this.posts]; // Reemplazar array
        }
        // --- [FIN FIX] ---
      },
      error: (err) => {
        console.error('Error al repostear:', err);
        post.reposts_count = previousRepostCount; // Revertir en caso de error
      },
    });
  }

  openComments(post: Post): void {
    this.selectedPostForComments = post;
    this.isCommentsModalOpen = true;
  }

  //openCommentModal(post: Post): void {
  //  this.selectedPostForModal = post;
  //  this.isCommentModalOpen = true;
  //}

  closeComments(): void {
    this.isCommentsModalOpen = false;
    this.selectedPostForComments = null;
  }

  onCommentAdded(): void {
    if (!this.selectedPostForComments) return;
    this.updatePostCommentsCount(this.selectedPostForComments.id, 1);
  }

  onCommentDeleted(): void {
    if (!this.selectedPostForComments) return;
    this.updatePostCommentsCount(this.selectedPostForComments.id, -1);
  }

  private updatePostCommentsCount(postId: number, delta: number): void {
    this.posts = this.posts.map((p) =>
      p.id === postId
        ? ({ ...p, comments_count: Math.max(0, (p.comments_count || 0) + delta) } as any)
        : p
    );
    if (this.selectedPostForComments && this.selectedPostForComments.id === postId) {
      this.selectedPostForComments =
        this.posts.find((p) => p.id === postId) || this.selectedPostForComments;
    }
  }
}
