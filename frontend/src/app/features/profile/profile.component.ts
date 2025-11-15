import { Component, inject, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CommonModule, DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { BehaviorSubject, Observable, of, forkJoin } from 'rxjs';
import { map, switchMap, tap, catchError } from 'rxjs/operators';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';
import { HttpErrorResponse } from '@angular/common/http';

import { User } from '../../core/models/user.model';
import { Post, Repost } from '../../core/models/post.model';
import {
  CreateReactionPayload,
  CreateRepostPayload,
  FollowPayload,
} from '../../core/models/api-payloads.model';
import { UserReactionStatus } from '../../core/models/user-reaction-status.model';

import { ProfileService } from '../../core/services/profile.service';
import { AuthService } from '../../core/services/auth.service';
import { PostService } from '../../core/services/post.service';
import { ImageUploadService } from '../../core/services/image-upload.service';
import { FollowService } from '../../core/services/follow.service';
import { InteractionService } from '../../core/services/interaction.service';
import { PostCommentsSectionComponent } from '../comments/components/post-comments-section/post-comments-section.component';
import { EchoService } from '../../core/services/echo.service';

import { ReactionTypeService } from '../../core/services/reaction-type.service';
import { ReactionType } from '../../core/models/reaction-type.model';
import { ReactionIconComponent } from '../reactions/reaction-icon/reaction-icon.component';
import { ReactionSelectorComponent } from '../reactions/reaction-selector/reaction-selector.component';
import { ReactionIdToNamePipe } from '../../core/pipes/reaction-id-to-name.pipe';

import { environment } from '../../../environments/environment';
import { MediaUrlPipe } from '../../core/pipes/media-url.pipe';

import { ReactionSummaryModalComponent } from '../reactions/reaction-summary-modal/reaction-summary-modal.component';
import { ReactionSummary } from '../../core/models/reaction-type.model';

function isRepost(item: Post | Repost): item is Repost {
  return (item as Repost).type === 'repost' || (item as Repost).post !== undefined;
}

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, 
            RouterLink, 
            DatePipe, 
            ReactiveFormsModule, 
            MediaUrlPipe, 
            PostCommentsSectionComponent,
            ReactionSelectorComponent,
            ReactionIconComponent,
            ReactionIdToNamePipe,
            ReactionSummaryModalComponent],
            
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss'],
})
export class ProfileComponent implements OnInit, OnDestroy {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private profileService = inject(ProfileService);
  private authService = inject(AuthService);
  private postService = inject(PostService);
  private imageUploadService = inject(ImageUploadService);
  private followService = inject(FollowService);
  private interactionService = inject(InteractionService);
  private echoService = inject(EchoService);
  private sanitizer = inject(DomSanitizer);
  private fb = inject(FormBuilder);
  private reactionTypeService = inject(ReactionTypeService);

  public apiUrlForImages = environment.apiUrl.replace('/api', '');
  private readonly LIKE_REACTION_TYPE_ID = 1;

  private refresh$ = new BehaviorSubject<void>(undefined);
  public user: User | null = null;
  public isLoading = true;
  public error: string | null = null;
  public isOwnProfile = false;
  public isFollowing = false;
  public userPosts: Post[] = [];
  public userReposts: Repost[] = [];
  public currentUserId: number | null = null;
  public userProfile$: Observable<User | null> = of(null);
  public isAdminOrMod: boolean = false;

  public reactionTypes: ReactionType[] = [];
  public openReactionMenuForItemId: string | null = null;

  public showReactionSummaryModal = false;
  public modalIsLoading = false;
  public modalSummary: ReactionSummary[] | null = null;

  public isEditingProfile = false;
  public selectedAvatarFile: File | null = null;
  public selectedBannerFile: File | null = null;
  public isUploadingAvatar = false;
  public isUploadingBanner = false;
  public avatarPreviewUrl: SafeUrl | null = null;
  public bannerPreviewUrl: SafeUrl | null = null;
  public currentTab: 'posts' | 'reposts' = 'posts';
  public cacheBustTs: number = Date.now();

  public openCommentItemId: string | null = null;
  public openPostId: number | null = null;
  public editingPostId: number | null = null;
  public editContent: string = '';

  public profileForm!: FormGroup;
  public passwordForm!: FormGroup;
  public submittingProfile = false;
  public submittingPassword = false;
  public apiErrorsProfile: any = null;
  public apiErrorsPassword: any = null;
  
  ngOnInit(): void {
    this.loadReactionTypes();
    const currentUser = this.authService.getCurrentUser();
    this.currentUserId = currentUser?.id ?? null;
    const role =
      (currentUser?.role || '').toLowerCase?.() ||
      (typeof currentUser?.role === 'string' ? (currentUser.role as string).toLowerCase() : '');
    this.isAdminOrMod = role === 'admin' || role === 'moderator';

    this.authService.currentUserChanges$?.subscribe((u) => {
      if (!u) return;
      const r =
        (u.role || '').toLowerCase?.() ||
        (typeof u.role === 'string' ? (u.role as string).toLowerCase() : '');
      this.isAdminOrMod = r === 'admin' || r === 'moderator';
      if (this.isOwnProfile && this.user) {
        this.user = {
          ...this.user,
          avatar: u.avatar,
          banner: u.banner,
          name: u.name || this.user.name,
          username: u.username || this.user.username,
        } as User;
      }

      this.userPosts = this.userPosts.map((p) =>
        p.user_id === u.id
          ? ({
              ...p,
              user: {
                ...p.user,
                avatar: u.avatar,
                banner: u.banner,
                name: u.name || p.user?.name,
                username: u.username || p.user?.username,
              },
            } as any)
          : p
      );
      this.userReposts = this.userReposts.map((item) => {
        const updatedRepost = { ...item };
        if (updatedRepost.user.id === u.id) { 
          updatedRepost.user = u;
        }
        if (updatedRepost.post.user.id === u.id) { 
          updatedRepost.post = { ...updatedRepost.post, user: u };
        }
        return updatedRepost;
      });

      this.cacheBustTs = Date.now();
    });

    this.userProfile$ = this.refresh$.pipe(
      tap(() => {
        this.isLoading = true;
        this.error = null;
        this.user = null;
        this.isOwnProfile = false;
        this.isFollowing = false;
        this.userPosts = [];
        this.userReposts = [];
        this.cancelAvatarChange();
        this.cancelBannerChange();
      }),
      switchMap(() => this.route.paramMap),
      switchMap((params) => {
        const usernameParam = params.get('username');
        const targetUsername = usernameParam || currentUser?.username;

        if (!targetUsername) {
          console.error('No se pudo determinar el perfil a cargar.');
          this.router.navigate(['/auth/login']);
          return of(null);
        }
        this.isOwnProfile = currentUser?.username === targetUsername;

        return this.profileService.getUserProfile(targetUsername).pipe(
          map((response: any) => (response ? (response.data as User) : null)),
          catchError((err: HttpErrorResponse) => {
            return of(null);
          })
        );
      }),
      tap((user: User | null) => {
        this.isLoading = false;
        this.user = user;
        if (user?.id) {
          console.log('Perfil cargado:', user);

          this.initForms(user);
          this.selectTab('posts');
          if (!this.isOwnProfile && this.currentUserId) {
            this.followService
              .getFollowing(this.currentUserId)
              .pipe(catchError(() => of([])))
              .subscribe((followingList: any) => {
                const list: any[] = Array.isArray(followingList)
                  ? followingList
                  : (followingList && (followingList as any).data) || [];
                this.isFollowing = list.some(
                  (u: any) => u.id === user.id || u.username === user.username
                );
              });
          }
        } else {
          if (!this.error) {
            this.error = 'Usuario no encontrado.';
          }
        }
      })
    );
  }

  private initForms(user: User): void {
    this.profileForm = this.fb.group({
      name: [user.name || '', [Validators.required, Validators.maxLength(100)]],
      email: [user.email || '', [Validators.required, Validators.email]],
      username: [user.username || ''],
    });

    this.passwordForm = this.fb.group({
      current_password: ['', [Validators.required]],
      new_password: ['', [Validators.required, Validators.minLength(8)]],
      new_password_confirmation: ['', [Validators.required]],
    });
  }
  loadUserPosts(userId: number): void {
    this.profileService
      .getUserPosts(userId.toString())
      .pipe(
        map((response: any) => (response ? (response.data as Post[]) : [])),
        tap((posts: Post[]) => {
          posts.forEach(post => {
            post.is_liked_by_user = post.user_reaction_status?.reaction_type_id === 1;
          });
        })
      )
      .subscribe({
        next: (postsWithLikeStatus: Post[]) => {
          this.userPosts = postsWithLikeStatus;
          this.listenToPostUpdates();
        },
        error: (err) => {
          console.error('Error al cargar posts:', err);
          this.userPosts = [];
        },
      });
  }

  loadUserReposts(userId: number): void {
    this.userReposts = [];
    this.profileService
      .getUserReposts(userId.toString())
      .pipe(
        map((response: any) => {
          return (Array.isArray(response) ? response : response?.data || []) as Repost[];
        }),
        tap((reposts: Repost[]) => {
          reposts.forEach(repost => {
            repost.post.is_liked_by_user = repost.post.user_reaction_status?.reaction_type_id === 1;
          });
        })
      )
      .subscribe({
        next: (hydratedReposts: Repost[]) => {
          this.userReposts = hydratedReposts;
          this.listenToPostUpdates(); 
        },
        error: (err) => {
          console.error('Error al cargar reposts:', err);
          this.userReposts = [];
        },
      });
  }

  selectTab(tab: 'posts' | 'reposts'): void {
    this.currentTab = tab;
    this.openCommentItemId = null;
    if (this.user?.id) {
      if (tab === 'posts') {
        this.loadUserPosts(this.user.id);
      } else if (tab === 'reposts') {
        this.loadUserReposts(this.user.id);
      }
    } else {
      console.warn('Intento de cambiar de pestaña sin usuario cargado.');
      this.userPosts = [];
      this.userReposts = [];
    }
  }
  private isRepost(item: Post | Repost): item is Repost {
    return (item as Repost).type === 'repost' || (item as Repost).post !== undefined;
  }

  getPostFromItem(item: Post | Repost): Post {
    return isRepost(item) ? (item as Repost).post : (item as Post);
  }

  getFeedItemId(item: Post | Repost): string {
    // Usamos la función helper que ya tienes para saber qué es
    if (this.isRepost(item)) {
      return `repost-${item.id}`;
    }
      // Si no es un repost, es un post
    return `post-${item.id}`;
  }
  openComments(item: Post | Repost): void {
    const uniqueId = this.getFeedItemId(item);
    if (this.openCommentItemId === uniqueId) {
      this.openCommentItemId = null;
    } else {
      this.openCommentItemId = uniqueId;
    }
  }
  onCommentAdded(item: Post | Repost): void {
    const postId = this.getPostFromItem(item).id;
    this.updatePostCommentsCount(postId, 1);
  }

  onCommentDeleted(item: Post | Repost): void {
    const postId = this.getPostFromItem(item).id;
    this.updatePostCommentsCount(postId, -1);
  }

  private updatePostCommentsCount(postId: number, delta: number): void {
    this.userPosts = this.userPosts.map((post) => {
      if (post.id === postId) {
        return { 
           ...post, 
           comments_count: Math.max(0, (post.comments_count || 0) + delta) 
         };
      }
      return post;
    });

    this.userReposts = this.userReposts.map((repost) => {
      if (repost.post.id === postId) {
        repost.post.comments_count = Math.max(0, (repost.post.comments_count || 0) + delta);
      }
      return repost;
    });
  }

  // --- Métodos de Edición de Perfil ---

  toggleEditProfile(): void {
    this.isEditingProfile = !this.isEditingProfile;
    if (!this.isEditingProfile) {
      this.resetPreviews();
    }
  }

  onAvatarSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedAvatarFile = file;
      const reader = new FileReader();
      reader.onload = (e) =>
        (this.avatarPreviewUrl = this.sanitizer.bypassSecurityTrustUrl(reader.result as string));
      reader.readAsDataURL(file);
    } else {
      this.cancelAvatarChange();
    }
    element.value = '';
  }

  onUploadAvatar(): void {
    if (!this.selectedAvatarFile || !this.isOwnProfile) return;
    this.isUploadingAvatar = true;
    this.imageUploadService.uploadAvatar(this.selectedAvatarFile).subscribe({
      next: (newAvatar) => {
        console.log('Avatar subido:', newAvatar);
        if (this.user) this.user.avatar = newAvatar;
        this.isUploadingAvatar = false;
        this.resetPreviews();
        this.authService.updateCurrentUser({
          ...this.user,
          avatar: newAvatar,
        });
        this.cacheBustTs = Date.now();
        this.refresh$.next();
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al subir avatar:', err);
        this.isUploadingAvatar = false;
        alert(`Error al subir avatar: ${err.error?.message || err.statusText}`);
      },
    });
  }

  cancelAvatarChange(): void {
    this.selectedAvatarFile = null;
    this.avatarPreviewUrl = null;
  }

  removeAvatar(): void {
    if (!this.user?.avatar || !this.isOwnProfile || !confirm('¿Eliminar avatar?')) return;
    this.imageUploadService.deleteAvatar().subscribe({
      next: () => {
        if (this.user) this.user.avatar = null;
        this.resetPreviews();
        this.authService.updateCurrentUser({ ...this.user, avatar: null });
      },
      error: (err) => {
        console.error('Error al eliminar avatar:', err);
        alert('Error al eliminar avatar.');
      },
    });
  }

  onBannerSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedBannerFile = file;
      const reader = new FileReader();
      reader.onload = (e) =>
        (this.bannerPreviewUrl = this.sanitizer.bypassSecurityTrustUrl(reader.result as string));
      reader.readAsDataURL(file);
    } else {
      this.cancelBannerChange();
    }
    element.value = '';
  }

  onUploadBanner(): void {
    if (!this.selectedBannerFile || !this.isOwnProfile) return;
    this.isUploadingBanner = true;
    this.imageUploadService.uploadBanner(this.selectedBannerFile).subscribe({
      next: (newBanner) => {
        console.log('Banner subido:', newBanner);
        if (this.user) this.user.banner = newBanner;
        this.isUploadingBanner = false;
        this.resetPreviews();
        this.authService.updateCurrentUser({
          ...this.user,
          banner: newBanner,
        });
        this.cacheBustTs = Date.now();
        this.refresh$.next();
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al subir banner:', err);
        this.isUploadingBanner = false;
        alert(`Error al subir banner: ${err.error?.message || err.statusText}`);
      },
    });
  }

  cancelBannerChange(): void {
    this.selectedBannerFile = null;
    this.bannerPreviewUrl = null;
  }

  removeBanner(): void {
    if (!this.user?.banner || !this.isOwnProfile || !confirm('¿Eliminar banner?')) return;
    this.imageUploadService.deleteBanner().subscribe({
      next: () => {
        if (this.user) this.user.banner = null;
        this.resetPreviews();
        this.authService.updateCurrentUser({ ...this.authService.getCurrentUser(), banner: null });
      },
      error: (err) => {
        console.error('Error al eliminar banner:', err);
        alert('Error al eliminar banner.');
      },
    });
  }

  saveProfileChangesAndExitEdit(): void {
    let uploadsRequired = false;
    if (this.selectedAvatarFile) {
      uploadsRequired = true;
      this.onUploadAvatar();
    }
    if (this.selectedBannerFile) {
      uploadsRequired = true;
      this.onUploadBanner();
    }
    this.isEditingProfile = false;
    if (!uploadsRequired) {
      this.resetPreviews();
    }
  }

  // --- Submit de edición de datos de perfil ---
  onSubmitProfile(): void {
    if (!this.profileForm || this.profileForm.invalid) return;
    const { name, email, username } = this.profileForm.value;
    this.submittingProfile = true;
    this.apiErrorsProfile = null;
    this.profileService.updateProfile({ name, email, username }).subscribe({
      next: (resp) => {
        if (this.user) {
          this.user.name = name;
          this.user.email = email;
          if (username) this.user.username = username;
        }
        const current = this.authService.getCurrentUser();
        this.authService.updateCurrentUser({
          ...current,
          name,
          email,
          username: username || current?.username,
        });
        this.submittingProfile = false;
        if (!this.selectedAvatarFile && !this.selectedBannerFile) {
          this.isEditingProfile = false;
        }
      },
      error: (err) => {
        console.error('Error al actualizar perfil:', err);
        this.apiErrorsProfile = err?.error || { general: ['No se pudo actualizar el perfil'] };
        this.submittingProfile = false;
      },
    });
  }

  // --- Submit de cambio de contraseña ---
  onSubmitPassword(): void {
    if (!this.passwordForm || this.passwordForm.invalid) return;
    const { current_password, new_password, new_password_confirmation } = this.passwordForm.value;
    if (new_password !== new_password_confirmation) {
      this.apiErrorsPassword = { new_password_confirmation: ['Las contraseñas no coinciden.'] };
      return;
    }
    this.submittingPassword = true;
    this.apiErrorsPassword = null;
    this.profileService
      .changePassword({ current_password, new_password, new_password_confirmation })
      .subscribe({
        next: () => {
          this.submittingPassword = false;
          this.passwordForm.reset();
          alert('Contraseña actualizada correctamente');
        },
        error: (err) => {
          console.error('Error al cambiar contraseña:', err);
          this.apiErrorsPassword = err?.error || { general: ['No se pudo cambiar la contraseña'] };
          this.submittingPassword = false;
        },
      });
  }

  cancelEditProfile(): void {
    this.isEditingProfile = false;
    this.resetPreviews();
  }

  resetPreviews(): void {
    this.cancelAvatarChange();
    this.cancelBannerChange();
  }

  // --- Métodos de Interacción ---

  toggleFollow(): void {
    if (!this.user?.id || !this.currentUserId || this.isOwnProfile) return;
    const targetUserId = this.user.id;
    const previousState = this.isFollowing;
    const previousFollowersCount = this.user.followers_count || 0;

    // Optimista
    this.isFollowing = !this.isFollowing;
    this.user.followers_count = this.isFollowing
      ? previousFollowersCount + 1
      : Math.max(0, previousFollowersCount - 1);

    const payload: FollowPayload = { following_id: targetUserId };
    const action$ = previousState
      ? this.followService.unfollowUser(payload)
      : this.followService.followUser(payload);

    action$.subscribe({
      error: (err) => {
        console.error('Error follow/unfollow:', err);
        this.isFollowing = previousState;
        this.user!.followers_count = previousFollowersCount;
        alert('Ocurrió un error al seguir/dejar de seguir.');
      },
    });
  }

  togglePostMenu(postId: number): void {
    this.openPostId = this.openPostId === postId ? null : postId;
  }

  onDeletePost(post: Post): void {
    this.openPostId = null;
    if (!post) return;
    if (confirm('¿Eliminar esta publicación?')) {
      const isOwner = !!this.currentUserId && this.currentUserId === post.user_id;
      const request$ = isOwner
        ? this.postService.deletePost(post.id)
        : this.isAdminOrMod
        ? this.postService.deletePostPrivileged(post.id)
        : null;
      if (!request$) return; // No permitido
      request$.subscribe({
        next: () => (this.userPosts = this.userPosts.filter((p) => p.id !== post.id)),
        error: (err) => console.error('Error al eliminar post:', err),
      });
    }
  }

  // ====== Editar Post (en perfil) ======
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
        this.userPosts = this.userPosts.map((p) =>
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
        console.error('Error al actualizar el post (perfil):', err);
        alert('No se pudo actualizar el post.');
      },
    });
  }

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
          // La UI optimista ya funciona
        },
        error: (err) => {
          console.error('Error al reaccionar al post (perfil):', err);
          post.is_liked_by_user = previousState.is_liked_by_user;
          post.reactions_count = previousState.reactions_count;
          alert('Error al dar Me Gusta.');
        },
      });
  }

  onToggleRepost(post: Post): void {
    const payload: CreateRepostPayload = { post_id: post.id };
    const previousRepostCount = post.reposts_count || 0;

    this.interactionService.createRepost(payload).subscribe({
      next: (updatedPost) => {
        //this.updatePostInArray(updatedPost);

        if (this.isOwnProfile && this.user?.id) {
          this.loadUserReposts(this.user.id);
        }
      },
      error: (err) => {
        console.error('Error repost:', err);
        post.reposts_count = previousRepostCount;
        alert('Error al repostear.');
      },
    });
  }

  /**
   * Helper para actualizar un post en AMBAS listas (posts y reposts).
   */
  private updatePostInArray(updatedPost: Post): void {
    this.userPosts = this.userPosts.map(post => {
      const local_is_liked_by_user = post.is_liked_by_user;
      if (post.id === updatedPost.id) {
        return {
          ...post,
          ...updatedPost,
          is_liked_by_user: local_is_liked_by_user,
          user: post.user 
        };
      }
      return post;
    });
    
    this.userReposts = this.userReposts.map(repost => {
      if (repost.post.id === updatedPost.id) {
        const local_is_liked_by_user = repost.post.is_liked_by_user;
        repost.post = {
          ...repost.post,
          ...updatedPost,
          is_liked_by_user: local_is_liked_by_user,
          user: repost.post.user
        };
      }
      return repost;
    });
  }

  private listenToPostUpdates(): void {
    const echo = this.echoService.echo;
    if (!echo) return;

    const postIds = new Set<number>();
    this.userPosts.forEach(post => postIds.add(post.id));
    this.userReposts.forEach(repost => postIds.add(repost.post.id));

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
        })
    });
  }
  ngOnDestroy(): void {
    const echo = this.echoService.echo;
    if (echo) {
      const postIds = new Set<number>();
      this.userPosts.forEach(post => postIds.add(post.id));
      this.userReposts.forEach(repost => postIds.add(repost.post.id));
      
      postIds.forEach(id => {
        echo.leaveChannel(`post.${id}`);
      });
    }
  }

  onDeleteRepost(repost: Repost): void {
    this.openPostId = null; // Cierra el menú
    if (!repost) return;

    const isOwner = !!this.currentUserId && this.currentUserId === repost.user.id;

    if (!isOwner && !this.isAdminOrMod) {
      console.warn('Usuario no autorizado para eliminar este repost.');
      return;
    }

    if (confirm('¿Estás seguro de que quieres eliminar este repost?')) {
      
      this.interactionService.deleteRepost(repost.id).subscribe({
        next: () => {
          // Lo quitamos de la lista local
          this.userReposts = this.userReposts.filter((r) => r.id !== repost.id);
          
          this.updatePostCountersOnDelete(repost.post.id);
        },
        error: (err) => {
          console.error('Error al eliminar el repost', err);
          alert('No se pudo eliminar el repost.');
        },
      });
    }
  }

  /**
   * Helper para decrementar el contador de reposts en un post
   * cuando un repost es eliminado.
   */
  private updatePostCountersOnDelete(deletedPostId: number): void {
    // Actualiza el contador en la lista de posts
    this.userPosts = this.userPosts.map(post => {
      if (post.id === deletedPostId) {
        return {
          ...post,
          reposts_count: Math.max(0, (post.reposts_count || 1) - 1)
        };
      }
      return post;
    });
    
    // Actualiza el contador en la lista de reposts (para otros reposts del mismo post)
    this.userReposts = this.userReposts.map(repost => {
      if (repost.post.id === deletedPostId) {
        repost.post = {
          ...repost.post,
          reposts_count: Math.max(0, (repost.post.reposts_count || 1) - 1)
        };
      }
      return repost;
    });
  }

  private loadReactionTypes(): void {
    this.reactionTypeService.getReactionTypes().subscribe({
      next: (types) => {
        this.reactionTypes = types;
      },
      error: (err) => {
        console.error('Error al cargar los tipos de reacción en Perfil:', err);
      }
    });
  }

  /**
   * Gestiona una reacción a un post (crear, actualizar o borrar).
   */
  onReact(item: Post | Repost, reactionTypeId: number): void {
    const post = this.getPostFromItem(item);

    const currentState = post.user_reaction_status;
    const currentReactionId = currentState?.reaction_type_id;

    const payload: CreateReactionPayload = {
      post_id: post.id,
      reaction_type_id: reactionTypeId,
    };

    const previousState = {
      status: post.user_reaction_status,
      count: post.reactions_count || 0,
      is_liked: post.is_liked_by_user 
    };

    if (currentReactionId === reactionTypeId) {
      payload.action = 'delete';
      post.user_reaction_status = { has_reacted: false, reaction_type_id: null };
      post.reactions_count = (post.reactions_count || 1) - 1;
      post.is_liked_by_user = false;

    } else if (currentReactionId) {
      payload.action = 'update';
      post.user_reaction_status = { has_reacted: true, reaction_type_id: reactionTypeId };
      post.is_liked_by_user = reactionTypeId === 1;

    } else {
      payload.action = 'create';
      post.user_reaction_status = { has_reacted: true, reaction_type_id: reactionTypeId };
      post.reactions_count = (post.reactions_count || 0) + 1;
      post.is_liked_by_user = reactionTypeId === 1;
    }

    this.interactionService.manageReaction(payload).subscribe({
      next: (response) => {
        // UI actualizada optimistamente
      },
      error: (err) => {
        console.error('Error al reaccionar al post (perfil):', err);
        // Revertir
        post.user_reaction_status = previousState.status;
        post.reactions_count = previousState.count;
        post.is_liked_by_user = previousState.is_liked;
      },
    });
  }

  /**
   * Abre el menú de reacciones para un ítem específico.
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
   * Cierra el menú de reacciones.
   */
  closeReactionMenu(): void {
    this.openReactionMenuForItemId = null;
  }

  /**
   * Da el color correcto al botón de "Reaccionar".
   */
  getReactionColor(post: Post): string {
    const id = post.user_reaction_status?.reaction_type_id;
    if (!id) return '#6b7280'; // gris

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
