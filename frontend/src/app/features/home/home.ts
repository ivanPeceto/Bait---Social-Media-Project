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
import { SearchService, UserSearchResult } from '../../core/services/search.service';
import { InteractionService } from '../../core/services/interaction.service';
import { CreateReactionPayload, CreateRepostPayload } from '../../core/models/api-payloads.model';
import { UserReactionStatus } from '../../core/models/user-reaction-status.model';
import { environment } from '../../../environments/environment';
import { MediaUrlPipe } from '../../core/pipes/media-url.pipe';
import { MultimediaContent } from '../../core/models/multimedia-content.model';
import { MultimediaContentService } from '../../core/services/multimedia-content.service';
import { PostCommentsSectionComponent } from '../comments/components/post-comments-section/post-comments-section.component';
import { EchoService } from '../../core/services/echo.service';
import { User } from '../../core/models/user.model';
import { PaginatedResponse } from '../../core/models/api-payloads.model';
import { Post, Repost } from '../../core/models/post.model';
import { ReactionTypeService } from '../../core/services/reaction-type.service';
import { ReactionType } from '../../core/models/reaction-type.model';
import { ReactionIconComponent } from '../reactions/reaction-icon/reaction-icon.component';
import { ReactionSelectorComponent } from '../reactions/reaction-selector/reaction-selector.component';
import { ReactionIdToNamePipe } from '../../core/pipes/reaction-id-to-name.pipe';
import { ReactionSummary } from '../../core/models/reaction-type.model';
import { ReactionSummaryModalComponent } from '../reactions/reaction-summary-modal/reaction-summary-modal.component';

function isRepost(item: Post | Repost): item is Repost {
  return (item as Repost).type === 'repost' || (item as Repost).post !== undefined;
}

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    DatePipe,
    RouterLink,
    MediaUrlPipe,
    PostCommentsSectionComponent,
    ReactionSelectorComponent,
    ReactionIconComponent,   
    ReactionIdToNamePipe,
    ReactionSummaryModalComponent,
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
  private reactionTypeService = inject(ReactionTypeService);

  private readonly LIKE_REACTION_TYPE_ID = 1;
  public readonly apiUrlForImages = environment.baseUrl;
  searchControl = new FormControl('');
  searchResults: any[] = [];
  isSearching = false;
  showResults = false;
  public currentUser: any;
  public posts: (Post | Repost)[] = [];
  public reactionTypes: ReactionType[] = [];
  public currentPage = 1;
  public lastPage = 1;
  public isLoadingMore = false;
  public postForm: FormGroup;
  public openPostId: number | null = null;
  public editingPostId: number | null = null;
  public editContent: string = '';
  public apiErrors: any = null;
  public echo: Echo<'pusher'> | null = null;

  public showReactionSummaryModal = false;
  public modalIsLoading = false;
  public modalSummary: ReactionSummary[] | null = null;

  public openReactionMenuForItemId: string | null = null;
  /** Almacena el ID único del ítem del feed (ej: "post-1" o "repost-1") */
  public openCommentItemId: string | null = null;

  public isLoading = false;
  public cacheBustTs = Date.now();
  public selectedFile: File | null = null;
  public previewUrl: string | null = null;
  public isUploadingMedia = false;

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

  private isRepost(item: Post | Repost): item is Repost {
    return (item as Repost).type === 'repost' || (item as Repost).post !== undefined;
  }

  getFeedItemId(item: Post | Repost): string {
  // Usamos la función helper que ya tienes para saber qué es
    if (this.isRepost(item)) {
      return `repost-${item.id}`;
    }
    // Si no es un repost, es un post
    return `post-${item.id}`;
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
        this.updatePostInArray(updated);
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

        this.posts = this.posts.map((item) => {
          
          if (isRepost(item)) {
            const updatedRepost = { ...item };
            if (updatedRepost.user.id === user.id) {
              updatedRepost.user = user; // Actualiza el reposter
            }
            if (updatedRepost.post.user.id === user.id) {
              updatedRepost.post = { ...updatedRepost.post, user: user }; // Actualiza el autor original
            }
            return updatedRepost;

          } else {
             if (item.user_id === user.id) {
              return { ...item, user: user };
            }
            return item;
          }
        });
      }
    });
    this.loadPosts();
    this.setupSearch();
    this.setupWebSocketListeners();
    this.loadReactionTypes();
  }

  /**
   * Limpia los listeners de WebSocket al destruir el componente.
   */
  ngOnDestroy(): void {
    const echo = this.echoService.echo;
    if (echo) {
      // Salir del canal privado
      if (this.currentUser) {
        echo.leave(`users.${this.currentUser.id}`);
      }
      // Salir de todos los canales de posts (directos o anidados)
      const postIds = new Set<number>();
      this.posts.forEach(item => {
        postIds.add(this.getPostFromItem(item).id);
      });
      postIds.forEach(id => {
        echo.leaveChannel(`post.${id}`);
      });
    }
  }

  private loadReactionTypes(): void {
    this.reactionTypeService.getReactionTypes().subscribe({
      next: (types) => {
        this.reactionTypes = types;
        console.log('Tipos de reacción cargados en Home:', this.reactionTypes); 
      },
      error: (err) => {
        console.error('Error al cargar los tipos de reacción en Home:', err);
      }
    });
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

    echo.private(`users.${this.currentUser.id}`)
      .listen('.NewPost', (data: { post: Post }) => {
        const newPost: Post = { ...data.post, type: 'post' };
        
        if (newPost && !this.posts.find(p => p.id === newPost.id && p.type === 'post')) {
          setTimeout(() => {
            this.posts = [newPost, ...this.posts];
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
            return of([]);
          }
          this.isSearching = true;
          this.showResults = true;
          const q = (query || '').toString();
          if (q.startsWith('@')) {
            const username = q.slice(1).trim();
            if (!username) {
              this.isSearching = false;
              this.searchResults = [];
              return of([]);
            }
            return this.searchService.searchByUsername(username);
          } else {
            return this.searchService.searchByName(q);
          }
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

    this.authService.currentUserChanges$.pipe(take(1)).subscribe((freshUser) => {
      
      // Llama al primer endpoint para crear el post
      this.postService.createPost(content).subscribe({
        next: (createdPost: Post) => {
          
          // Comprueba si hay un archivo seleccionado
          if (this.selectedFile) {
            this.isUploadingMedia = true;
            
            // Llama al segundo endpoint para subir la imagen
            this.multimediaService.uploadToPost(createdPost.id, this.selectedFile).subscribe({
              
              // ÉXITO (con imagen)
              next: (media: MultimediaContent) => {
                
                // CREA el objeto final aquí, AHORA que tienes la 'media'
                const hydratedPost: Post = {
                  ...createdPost,
                  user: freshUser || createdPost.user,
                  user_id: freshUser?.id || createdPost.user_id,
                  multimedia_contents: [media], 
                  type: 'post'
                };

                this.cacheBustTs = Date.now();
                this.posts = [hydratedPost, ...this.posts];

                // Resetea todo
                this.isUploadingMedia = false;
                this.onRemoveSelectedImage();
                this.postForm.reset();
              },
              
              // ERROR (al subir imagen)
              error: (err) => {
                console.error('Error al subir imagen del post:', err);
                // El post se creó, pero la imagen falló.
                // Lo añadimos sin 'multimedia_contents'.
                const hydratedPost: Post = {
                  ...createdPost,
                  user: freshUser || createdPost.user,
                  user_id: freshUser?.id || createdPost.user_id,
                  multimedia_contents: [], 
                  type: 'post'
                };
                this.cacheBustTs = Date.now();
                this.posts = [hydratedPost, ...this.posts];
                this.isUploadingMedia = false;
                this.onRemoveSelectedImage();
                this.postForm.reset();
                alert('El post se creó, pero la imagen no pudo subirse.');
              },
            });

          } else {
            // ÉXITO (sin imagen)
            // No hay archivo, así que creamos el post hidratado inmediatamente
            const hydratedPost: Post = {
              ...createdPost,
              user: freshUser || createdPost.user,
              user_id: freshUser?.id || createdPost.user_id,
              multimedia_contents: [], 
              type: 'post'
            };
            this.cacheBustTs = Date.now();
            this.posts = [hydratedPost, ...this.posts];
            this.postForm.reset();
          }
        },
        // ERROR (al crear el post)
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
  }

  /**
   * Carga la lista inicial de posts y luego verifica el estado de 'like'
   * del usuario actual para cada post mediante llamadas adicionales.
   */
  loadPosts(): void {
    this.loadReactionTypes();
    
    // Evitar cargas múltiples si ya se está cargando
    if (this.isLoading || this.isLoadingMore) return;

    if (this.currentPage === 1) {
      this.isLoading = true;
    } else {
      this.isLoadingMore = true;
    }

    this.postService.getFeed(this.currentPage)
      .pipe(
        map((response: any) => {
          this.currentPage = response.current_page;
          this.lastPage = response.last_page;
          
          return (response.data || []) as (Post | Repost)[]; 
        }),
        catchError((err) => {
          console.error('Error al cargar el feed:', err);
          return of([]);
        }),
      )
      .subscribe({
        next: (feedItems: (Post | Repost)[]) => {
          if (this.currentPage === 1) {
            // Si es la página 1, reemplazamos el contenido
            this.posts = feedItems;
          } else {
            // Si son páginas siguientes, las añadimos (para scroll infinito)
            this.posts = [...this.posts, ...feedItems];
          }
          
          this.isLoading = false;
          this.isLoadingMore = false;
          this.listenToPostUpdates(); 
        },
        error: (err) => {
          console.error('Error en la suscripción de loadPosts:', err);
          this.isLoading = false;
          this.isLoadingMore = false;
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

    const postIds = new Set<number>();
    this.posts.forEach(item => {
      postIds.add(this.getPostFromItem(item).id);
    });

    postIds.forEach(id => {
      echo.leaveChannel(`post.${id}`);
    });

    postIds.forEach(id => {
      echo.channel(`post.${id}`)
        .listen('.PostReactionUpdated', (data: { post: Post }) => {
          this.updatePostInArray(data.post);
        })
        .listen('.PostRepostUpdated', (data: { post: Post }) => {
          this.updatePostInArray(data.post);
        });
    });
  }

  /**
   * Helper para actualizar un post en el array 'this.posts'.
   * Ahora busca el 'updatedPost' tanto si es un Post directo
   * como si es un Post anidado dentro de un Repost.
   */
  private updatePostInArray(updatedPost: Post): void {
    this.posts = this.posts.map(item => {
    const post = this.getPostFromItem(item);
      if (post.id === updatedPost.id) {
        const local_is_liked_by_user = post.is_liked_by_user;
        if (isRepost(item)) {
          return {
            ...item,
            post: {
              ...post,
              ...updatedPost,
              is_liked_by_user: local_is_liked_by_user,
              user: post.user 
            }
          };
        }
        return {
          ...item,
          ...updatedPost,
          is_liked_by_user: local_is_liked_by_user,
          user: item.user 
        };
      }
      return item;
    });
  }
  /**
   * Gestiona una reacción a un post (crear, actualizar o borrar).
   * @param item El Post o Repost del feed.
   * @param reactionTypeId El ID del tipo de reacción que el usuario clickeó (ej: 1 para 'like', 2 para 'love').
   */
  onReact(item: Post | Repost, reactionTypeId: number): void {
    const post = this.getPostFromItem(item);

    const currentState = post.user_reaction_status;
    const currentReactionId = currentState?.reaction_type_id;

    const payload: CreateReactionPayload = {
      post_id: post.id,
      reaction_type_id: reactionTypeId,
    };

    // Guardamos el estado anterior para revertir si falla
    const previousState = {
      status: post.user_reaction_status,
      count: post.reactions_count || 0,
    };

    // Lógica de UI Optimista
    if (currentReactionId === reactionTypeId) {
      // 1. Clickeó la misma reacción: BORRAR
      payload.action = 'delete';
      post.user_reaction_status = { has_reacted: false, reaction_type_id: null };
      post.reactions_count = (post.reactions_count || 1) - 1;
      post.is_liked_by_user = false; // (por compatibilidad)

    } else if (currentReactionId) {
      // 2. Clickeó una reacción diferente: ACTUALIZAR
      payload.action = 'update';
      post.user_reaction_status = { has_reacted: true, reaction_type_id: reactionTypeId };
      // El contador de reacciones totales no cambia
      post.is_liked_by_user = reactionTypeId === 1; // (por compatibilidad)

    } else {
      // 3. No tenía reacción: CREAR
      payload.action = 'create';
      post.user_reaction_status = { has_reacted: true, reaction_type_id: reactionTypeId };
      post.reactions_count = (post.reactions_count || 0) + 1;
      post.is_liked_by_user = reactionTypeId === 1; // (por compatibilidad)
    }

    // Llamada al servicio
    this.interactionService.manageReaction(payload).subscribe({
      next: (response) => {
        // Éxito. La UI ya está actualizada.
        // El WS actualizará a otros, pero NO al usuario actual
        // (por eso el 'updatePostInArray' sigue siendo necesario
        // para los contadores que vienen del WS)
      },
      error: (err) => {
        // ¡Error! Revertimos la UI
        console.error('Error al reaccionar al post:', err);
        post.user_reaction_status = previousState.status;
        post.reactions_count = previousState.count;
        post.is_liked_by_user = previousState.status?.reaction_type_id === 1;
      },
    });
  }

  /**
   * Abre el menú de reacciones para un ítem específico.
   * Si ya está abierto, lo cierra.
   */
  toggleReactionMenu(item: Post | Repost): void {
    const uniqueId = this.getFeedItemId(item);
    if (this.openReactionMenuForItemId === uniqueId) {
      this.openReactionMenuForItemId = null;
    } else {
      this.openReactionMenuForItemId = uniqueId;
    }
  }

  /**
   * Cierra el menú de reacciones. Útil para (mouseleave).
   */
  closeReactionMenu(): void {
    this.openReactionMenuForItemId = null;
  }

  /**
   * Función helper para darle el color correcto al botón de "Reaccionar"
   * basado en la reacción actual.
   */
  getReactionColor(post: Post): string {
    const id = post.user_reaction_status?.reaction_type_id;
    if (!id) return '#6b7280';

    switch(id) {
      case 1: return '#007bff'; // like
      case 2: return '#e0245e'; // love
      case 3: return '#f4b400'; // haha
      case 4: return '#1da1f2'; // wow
      case 5: return '#ffad1f'; // sad
      case 6: return '#d93a00'; // angry
      default: return '#6b7280';
    }
  }
  /**
   * Gestiona el click en el botón de 'Like'. Actualiza la UI de forma optimista
   * y luego llama al servicio para crear/eliminar la reacción en el backend.
   * @param post El objeto Post al que se le dio like/unlike.
   */
  onToggleLike(item: Post | Repost): void {
    const post = this.getPostFromItem(item);

    const previousState = {
      is_liked_by_user: post.is_liked_by_user,
      reactions_count: post.reactions_count || 0,
    };

    const payload: CreateReactionPayload = {
      post_id: post.id,
      reaction_type_id: this.LIKE_REACTION_TYPE_ID,
    };

    if (post.is_liked_by_user) {
      post.reactions_count = (post.reactions_count || 1) - 1;
      post.is_liked_by_user = false;
      payload.action = 'delete';
    } else {
      post.reactions_count = (post.reactions_count || 0) + 1;
      post.is_liked_by_user = true;
      payload.action = 'create'; 
    }

    this.interactionService.manageReaction(payload).subscribe({
      next: (response) => {
        //this.updatePostInArray(post);
        // Éxito. La UI ya se actualizó optimistamente.
        // El evento WS actualizará a otros usuarios.
      },
      error: (err) => {
        // Si falla, revertimos la UI al estado anterior
        console.error('Error al reaccionar al post:', err);
        post.is_liked_by_user = previousState.is_liked_by_user;
        post.reactions_count = previousState.reactions_count;
        this.updatePostInArray(post);
      },
    });
  }

  getPostFromItem(item: Post | Repost): Post {
    return this.isRepost(item) ? (item as Repost).post : (item as Post);
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
          this.posts = this.posts.filter(item => {
            return this.getPostFromItem(item).id !== post.id;
          });
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

    this.interactionService.createRepost(payload).subscribe({
      next: (updatedPost) => {
        this.updatePostInArray(updatedPost);
      },
      error: (err) => {
        console.error('Error al repostear:', err);
        post.reposts_count = previousRepostCount; 
      },
    });
  }

  asRepost(item: Post | Repost): Repost {
    return item as Repost;
  }

  /**
   * Alterna la visibilidad de la sección de comentarios para un post.
   */
  openComments(item: Post | Repost): void {
    const uniqueId = this.getFeedItemId(item);

    if (this.openCommentItemId === uniqueId) {
      // Si ya está abierto, ciérralo
      this.openCommentItemId = null;
    } else {
      // Si está cerrado, ábrelo
      this.openCommentItemId = uniqueId;
    }
  }

  /**
   * Actualiza el contador de comentarios en el post principal.
   * Llamado por (commentAdded)
   */
  onCommentAdded(): void {
    if (!this.openCommentItemId) return;
    
    const item = this.posts.find(p => this.getFeedItemId(p) === this.openCommentItemId);
    if (!item) return;
    
    const postId = this.getPostFromItem(item).id;
    this.updatePostCommentsCount(postId, 1);
  }

  /**
   * Actualiza el contador de comentarios en el post principal.
   * Llamado por (commentDeleted)
   */
  onCommentDeleted(): void {
    if (!this.openCommentItemId) return;

    const item = this.posts.find(p => this.getFeedItemId(p) === this.openCommentItemId);
    if (!item) return;

    const postId = this.getPostFromItem(item).id;
    this.updatePostCommentsCount(postId, -1);
  }

  /**
   * Helper privado para encontrar y actualizar el contador en el array de posts.
   */
  private updatePostCommentsCount(postId: number, delta: number): void {
    this.posts = this.posts.map((item) => {
      const post = this.getPostFromItem(item);
      
      if (post.id === postId) {
         const updatedPost: Post = { 
           ...post, 
           comments_count: Math.max(0, (post.comments_count || 0) + delta) 
         };

         if (isRepost(item)) {
           return { ...item, post: updatedPost };
         }
         return updatedPost;
      }
      return item;
    });
  }
 
  openReactionSummary(post: Post): void {
    this.showReactionSummaryModal = true;
    this.modalIsLoading = true;
    this.modalSummary = null; 

    this.postService.getReactionSummary(post.id).subscribe({
      next: (summary) => {
        this.modalSummary = summary;
        this.modalIsLoading = false;
      },
      error: (err) => {
        console.error('Error al cargar el sumario de reacciones:', err);
        this.modalIsLoading = false;
        // (Opcional) puedes cerrar el modal si falla
        // this.showReactionSummaryModal = false; 
      }
    });
  }

  closeReactionSummary(): void {
    this.showReactionSummaryModal = false;
    this.modalSummary = null;
  }
}
