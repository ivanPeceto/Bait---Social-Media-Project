import { Component, inject, OnInit } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CommonModule, DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { BehaviorSubject, Observable, of, forkJoin } from 'rxjs';
import { map, switchMap, tap, catchError } from 'rxjs/operators';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';
import { HttpErrorResponse } from '@angular/common/http';

import { User } from '../../core/models/user.model';
import { Post } from '../../core/models/post.model';
import { CreateReactionPayload, CreateRepostPayload, FollowPayload } from '../../core/models/api-payloads.model';
import { UserReactionStatus } from '../../core/models/user-reaction-status.model';

import { ProfileService } from '../../core/services/profile.service';
import { AuthService } from '../../core/services/auth.service';
import { PostService } from '../../core/services/post.service';
import { ImageUploadService } from '../../core/services/image-upload.service';
import { FollowService } from '../../core/services/follow.service';
import { InteractionService } from '../../core/services/interaction.service';

import { environment } from '../../../environments/environment';
import { MediaUrlPipe } from '../../core/pipes/media-url.pipe';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterLink, DatePipe, ReactiveFormsModule, MediaUrlPipe],
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private profileService = inject(ProfileService);
  private authService = inject(AuthService);
  private postService = inject(PostService);
  private imageUploadService = inject(ImageUploadService);
  private followService = inject(FollowService);
  private interactionService = inject(InteractionService);
  private sanitizer = inject(DomSanitizer);
  private fb = inject(FormBuilder);

  public apiUrlForImages = environment.apiUrl.replace('/api', '');
  private readonly LIKE_REACTION_TYPE_ID = 1;

  private refresh$ = new BehaviorSubject<void>(undefined);
  public user: User | null = null;
  public isLoading = true;
  public error: string | null = null;
  public isOwnProfile = false;
  public isFollowing = false;
  public userPosts: Post[] = [];
  public userReposts: Post[] = []; 
  public openPostId: number | null = null; 
  public editingPostId: number | null = null; 
  public editContent: string = '';
  public currentUserId: number | null = null;
  public userProfile$: Observable<User | null> = of(null);
  public isAdminOrMod: boolean = false;

  public isEditingProfile = false;
  public selectedAvatarFile: File | null = null;
  public selectedBannerFile: File | null = null;
  public isUploadingAvatar = false;
  public isUploadingBanner = false;
  public avatarPreviewUrl: SafeUrl | null = null;
  public bannerPreviewUrl: SafeUrl | null = null; 
  public currentTab: 'posts' | 'reposts' = 'posts';
  public cacheBustTs: number = Date.now();

  public profileForm!: FormGroup;
  public passwordForm!: FormGroup;
  public submittingProfile = false;
  public submittingPassword = false;
  public apiErrorsProfile: any = null;
  public apiErrorsPassword: any = null;

  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    this.currentUserId = currentUser?.id ?? null;
    const role = (currentUser?.role || '').toLowerCase?.() || (typeof currentUser?.role === 'string' ? (currentUser.role as string).toLowerCase() : '');
    this.isAdminOrMod = role === 'admin' || role === 'moderator';

    this.authService.currentUserChanges$?.subscribe((u) => {
      if (!u) return;
      const r = (u.role || '').toLowerCase?.() || (typeof u.role === 'string' ? (u.role as string).toLowerCase() : '');
      this.isAdminOrMod = r === 'admin' || r === 'moderator';
      if (this.isOwnProfile && this.user) {
        this.user = { ...this.user, avatar: u.avatar, banner: u.banner, name: u.name || this.user.name, username: u.username || this.user.username } as User;
      }
      
      this.userPosts = this.userPosts.map(p => p.user_id === u.id ? ({ ...p, user: { ...p.user, avatar: u.avatar, banner: u.banner, name: u.name || p.user?.name, username: u.username || p.user?.username } } as any) : p);
      this.userReposts = this.userReposts.map(p => p.user_id === u.id ? ({ ...p, user: { ...p.user, avatar: u.avatar, banner: u.banner, name: u.name || p.user?.name, username: u.username || p.user?.username } } as any) : p);
      this.cacheBustTs = Date.now();
    });

    this.userProfile$ = this.refresh$.pipe(
      tap(() => { 
        this.isLoading = true; this.error = null; this.user = null; this.isOwnProfile = false;
        this.isFollowing = false; this.userPosts = []; this.userReposts = [];
        this.cancelAvatarChange(); this.cancelBannerChange(); 
      }),
      switchMap(() => this.route.paramMap),
      switchMap(params => {
        const usernameParam = params.get('username');
        const targetUsername = usernameParam || currentUser?.username;

        if (!targetUsername) {
          console.error("No se pudo determinar el perfil a cargar.");
          this.router.navigate(['/auth/login']);
          return of(null);
        }
        this.isOwnProfile = currentUser?.username === targetUsername;

        return this.profileService.getUserProfile(targetUsername).pipe(
           map((response: any) => response ? response.data as User : null), 
           catchError((err: HttpErrorResponse) => { return of(null); })
        );
      }),
      tap((user: User | null) => { 
        this.isLoading = false;
        this.user = user; 
        if (user?.id) {
          console.log("Perfil cargado:", user);
          
          this.initForms(user);
          this.selectTab('posts'); 
          if (!this.isOwnProfile && this.currentUserId) {
            this.followService.getFollowing(this.currentUserId).pipe(
              catchError(() => of([]))
            ).subscribe((followingList: any) => {
              
              const list: any[] = Array.isArray(followingList)
                ? followingList
                : ((followingList && (followingList as any).data) || []);
              this.isFollowing = list.some((u: any) => (u.id === user.id) || (u.username === user.username));
            });
          }
        } else {
          if (!this.error) { this.error = 'Usuario no encontrado.'; }
        }
      })
    );
  }

  private initForms(user: User): void {
    this.profileForm = this.fb.group({
      name: [user.name || '', [Validators.required, Validators.maxLength(100)]],
      email: [user.email || '', [Validators.required, Validators.email]],
      username: [user.username || '']
    });

    this.passwordForm = this.fb.group({
      current_password: ['', [Validators.required]],
      new_password: ['', [Validators.required, Validators.minLength(8)]],
      new_password_confirmation: ['', [Validators.required]]
    });
  }
  loadUserPosts(userId: number): void {
    this.profileService.getUserPosts(userId.toString()).pipe(   
      map((response: any) => response ? response.data as Post[] : []), 
      switchMap((initialPosts: Post[]) => { 
        if (initialPosts.length === 0) return of([]);
        const reactionChecks$: Observable<UserReactionStatus>[] = initialPosts.map(post =>
          this.interactionService.checkUserReaction(post.id).pipe(
              catchError(() => of({ has_reacted: false, reaction_type_id: null })) 
          )
        );
        return forkJoin(reactionChecks$).pipe(
          map((reactionStatuses: UserReactionStatus[]) =>
            initialPosts.map((post, index) => {
              post.is_liked_by_user = reactionStatuses[index].has_reacted &&
                                      reactionStatuses[index].reaction_type_id === this.LIKE_REACTION_TYPE_ID;
              return post;
            })
          )
        );
      })
    ).subscribe({
      next: (postsWithLikeStatus: Post[]) => this.userPosts = postsWithLikeStatus,
      error: (err) => {
          console.error('Error al cargar posts o verificar likes:', err);
          this.userPosts = []; 
      }
    });
  }

  loadUserReposts(userId: number): void {
      this.userReposts = [];
      this.profileService.getUserReposts(userId.toString()).pipe(
        map((response: any) => Array.isArray(response) ? response : (response?.data || [])),
        switchMap((reposts: any[]) => {
          if (!reposts || reposts.length === 0) return of([]);
          
          const fetchPosts$: Observable<Post>[] = reposts.map(r => this.postService.getPostById(r.post_id));
          return forkJoin(fetchPosts$);
        }),
        switchMap((posts: Post[]) => {
          if (!posts || posts.length === 0) return of([]);
          
          const reactionChecks$: Observable<UserReactionStatus>[] = posts.map(post =>
            this.interactionService.checkUserReaction(post.id).pipe(
              catchError(() => of({ has_reacted: false, reaction_type_id: null }))
            )
          );
          return forkJoin(reactionChecks$).pipe(
            map((statuses: UserReactionStatus[]) =>
              posts.map((post, i) => {
                post.is_liked_by_user = statuses[i].has_reacted && statuses[i].reaction_type_id === this.LIKE_REACTION_TYPE_ID;
                return post;
              })
            )
          );
        })
      ).subscribe({
        next: (hydratedPosts: Post[]) => this.userReposts = hydratedPosts,
        error: (err) => {
          console.error('Error al cargar reposts:', err);
          this.userReposts = [];
        }
      });
  }

  selectTab(tab: 'posts' | 'reposts'): void {
    this.currentTab = tab;
    if (this.user?.id) { 
        if (tab === 'posts') {
          this.loadUserPosts(this.user.id); 
        } else if (tab === 'reposts') {
          this.loadUserReposts(this.user.id); 
        }
    } else {
        console.warn("Intento de cambiar de pestaña sin usuario cargado.");
        this.userPosts = []; 
        this.userReposts = [];
    }
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
      reader.onload = e => this.avatarPreviewUrl = this.sanitizer.bypassSecurityTrustUrl(reader.result as string);
      reader.readAsDataURL(file);
    } else { this.cancelAvatarChange(); }
     element.value = "";  
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
        this.authService.updateCurrentUser({ ...this.authService.getCurrentUser(), avatar: newAvatar });
        this.cacheBustTs = Date.now();
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al subir avatar:', err);
        this.isUploadingAvatar = false;
        alert(`Error al subir avatar: ${err.error?.message || err.statusText}`);
      }
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
              if(this.user) this.user.avatar = null;
              this.resetPreviews();
              this.authService.updateCurrentUser({ ...this.authService.getCurrentUser(), avatar: null });
          },
          error: (err) => { console.error("Error al eliminar avatar:", err); alert("Error al eliminar avatar."); }
      });
  }

  onBannerSelected(event: Event): void { 
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedBannerFile = file;
      const reader = new FileReader();
      reader.onload = e => this.bannerPreviewUrl = this.sanitizer.bypassSecurityTrustUrl(reader.result as string);
      reader.readAsDataURL(file);
    } else { this.cancelBannerChange(); }
     element.value = "";
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
        this.authService.updateCurrentUser({ ...this.authService.getCurrentUser(), banner: newBanner });
        this.cacheBustTs = Date.now();
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al subir banner:', err);
        this.isUploadingBanner = false;
        alert(`Error al subir banner: ${err.error?.message || err.statusText}`);
      }
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
              if(this.user) this.user.banner = null;
              this.resetPreviews();
              this.authService.updateCurrentUser({ ...this.authService.getCurrentUser(), banner: null });
          },
          error: (err) => { console.error("Error al eliminar banner:", err); alert("Error al eliminar banner."); }
      });
  }

  saveProfileChangesAndExitEdit(): void {
      let uploadsRequired = false;
      if (this.selectedAvatarFile) { uploadsRequired = true; this.onUploadAvatar(); }
      if (this.selectedBannerFile) { uploadsRequired = true; this.onUploadBanner(); }
      this.isEditingProfile = false;
      if (!uploadsRequired) { this.resetPreviews(); }
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
        this.authService.updateCurrentUser({ ...current, name, email, username: username || current?.username });
        this.submittingProfile = false;
        if (!this.selectedAvatarFile && !this.selectedBannerFile) {
          this.isEditingProfile = false;
        }
      },
      error: (err) => {
        console.error('Error al actualizar perfil:', err);
        this.apiErrorsProfile = err?.error || { general: ['No se pudo actualizar el perfil'] };
        this.submittingProfile = false;
      }
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
    this.profileService.changePassword({ current_password, new_password, new_password_confirmation }).subscribe({
      next: () => {
        this.submittingPassword = false;
        this.passwordForm.reset();
        alert('Contraseña actualizada correctamente');
      },
      error: (err) => {
        console.error('Error al cambiar contraseña:', err);
        this.apiErrorsPassword = err?.error || { general: ['No se pudo cambiar la contraseña'] };
        this.submittingPassword = false;
      }
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
    this.user.followers_count = this.isFollowing ? previousFollowersCount + 1 : Math.max(0, previousFollowersCount - 1);

    const payload: FollowPayload = { following_id: targetUserId };
    const action$ = previousState ? this.followService.unfollowUser(payload) : this.followService.followUser(payload);

    action$.subscribe({
      error: (err) => {
        console.error('Error follow/unfollow:', err);
        this.isFollowing = previousState;
        this.user!.followers_count = previousFollowersCount;
        alert('Ocurrió un error al seguir/dejar de seguir.');
      }
    });
  }

  togglePostMenu(postId: number): void {
     this.openPostId = (this.openPostId === postId) ? null : postId;
  }

  onDeletePost(post: Post): void {
     this.openPostId = null;
     if (!post) return;
     if (confirm('¿Eliminar esta publicación?')) {
       const isOwner = !!this.currentUserId && this.currentUserId === post.user_id;
       const request$ = isOwner
         ? this.postService.deletePost(post.id)
         : (this.isAdminOrMod ? this.postService.deletePostPrivileged(post.id) : null);
       if (!request$) return; // No permitido
       request$.subscribe({
         next: () => this.userPosts = this.userPosts.filter(p => p.id !== post.id),
         error: (err) => console.error('Error al eliminar post:', err)
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
        this.userPosts = this.userPosts.map(p => p.id === post.id ? ({ ...p, content_posts: updated.content_posts, updated_at: updated.updated_at }) as any : p);
        this.editingPostId = null;
        this.editContent = '';
      },
      error: (err) => {
        console.error('Error al actualizar el post (perfil):', err);
        alert('No se pudo actualizar el post.');
      }
    });
  }

  onToggleLike(post: Post): void {
    const previousState = { is_liked_by_user: post.is_liked_by_user, reactions_count: post.reactions_count || 0 };
    post.is_liked_by_user = !post.is_liked_by_user;
    post.reactions_count = post.is_liked_by_user ? previousState.reactions_count + 1 : Math.max(0, previousState.reactions_count - 1);

    const payload: CreateReactionPayload = { post_id: post.id, reaction_type_id: this.LIKE_REACTION_TYPE_ID };
    this.interactionService.toggleReaction(payload).subscribe({
      error: (err) => {
        console.error('Error like:', err);
        post.is_liked_by_user = previousState.is_liked_by_user;
        post.reactions_count = previousState.reactions_count;
        alert('Error al dar Me Gusta.');
      },
    });
  }

  onToggleRepost(post: Post): void {
    const payload: CreateRepostPayload = { post_id: post.id };
    const optimisticShouldMutateList = this.isOwnProfile && this.currentTab === 'reposts';
    let reverted = false;
    if (optimisticShouldMutateList) {
      const exists = this.userReposts.some(p => p.id === post.id);
      if (!exists) {
        this.userReposts = [post, ...this.userReposts];
      }
    }
    this.interactionService.toggleRepost(payload).subscribe({
      next: (updatedPost) => {
         console.log('toggleRepost response:', updatedPost);
         if (updatedPost && typeof updatedPost.reposts_count === 'number') {
           post.reposts_count = updatedPost.reposts_count;
         }
         console.log('Repost action successful, new count:', post.reposts_count);
         if (this.isOwnProfile && this.user?.id) {
           this.loadUserReposts(this.user.id);
         }
      },
      error: (err) => {
        console.error('Error repost:', err);
        if (optimisticShouldMutateList && !reverted) {
          // revertir optimismo
          this.userReposts = this.userReposts.filter(p => p.id !== post.id);
          reverted = true;
        }
        alert('Error al repostear.');
      },
    });
  }

} 