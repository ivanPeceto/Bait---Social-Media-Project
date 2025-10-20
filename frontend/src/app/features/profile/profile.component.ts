import { Component, inject, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink, RouterOutlet } from '@angular/router';
import { CommonModule, DatePipe } from '@angular/common';
import { BehaviorSubject, Observable, EMPTY } from 'rxjs'; 
import { map, switchMap, tap } from 'rxjs/operators';
import { ProfileService } from './services/profile.service'; 
import { AuthService } from '../auth/services/auth.service';
import { User } from '../../core/models/user.model';
import { PostService , Post } from '../post/services/post.service'; 
import { environment } from '../../../environments/environment'; 
import { HttpErrorResponse } from '@angular/common/http'; 

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterLink, DatePipe, RouterOutlet], 
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private profileService = inject(ProfileService);
  private authService = inject(AuthService);
  private postService = inject(PostService);

  public apiUrlForImages = environment.apiUrl.replace('/api', ''); 

  private refresh$ = new BehaviorSubject<void>(undefined); 
  public userProfile$!: Observable<User>;                
  public isOwnProfile = false;                           
  public userPosts: Post[] = [];                      
  public openPostId: number | null = null;          
  public currentUserId: number | null = null;     


  public selectedAvatarFile: File | null = null;       
  public selectedBannerFile: File | null = null;       
  public isUploadingAvatar = false;                    
  public isUploadingBanner = false;                     
  public avatarPreviewUrl: string | ArrayBuffer | null = null; 
  public bannerPreviewUrl: string | ArrayBuffer | null = null; 

  private profileUserIdToLoad: string | null = null;


  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    this.currentUserId = currentUser?.id;

    const profileLoader$ = this.refresh$.pipe(
      switchMap(() => this.route.paramMap),
      switchMap(params => {
        const userIdParam = params.get('id');
        this.profileUserIdToLoad = userIdParam ? userIdParam : this.currentUserId?.toString() ?? null;

        if (!this.profileUserIdToLoad) {
          console.error("No se pudo determinar el ID del perfil.");
          return EMPTY; 
        }

        this.isOwnProfile = this.currentUserId?.toString() === this.profileUserIdToLoad;

        this.selectedAvatarFile = null;
        this.selectedBannerFile = null;
        this.avatarPreviewUrl = null;
        this.bannerPreviewUrl = null;
        this.isUploadingAvatar = false;
        this.isUploadingBanner = false;

        if (this.isOwnProfile) {
            return this.profileService.getOwnProfile();
        } else {
            return this.profileService.getUserProfile(this.profileUserIdToLoad);
        }
      })
    );


    this.userProfile$ = profileLoader$.pipe(
        map((response: any) => response.data as User),
        tap((user: User) => { 
          if (user && user.id) {
            console.log("Perfil cargado correctamente:", user); 
            this.loadUserPosts(user.id.toString());
          } else {
            console.error("Perfil inválido después de extraer 'data':", user);
            this.userPosts = [];
          }
        })
    );
  }



  loadUserPosts(userId: string): void {
    this.profileService.getUserPosts(userId).subscribe({
      next: (postsResponse: any) => {
          if (postsResponse && postsResponse.data) {
             this.userPosts = postsResponse.data || []; 
          } else {
             this.userPosts = postsResponse || []; 
          }
          console.log(`Posts cargados para ${userId}:`, this.userPosts);
       },
      error: (err) => {
        console.error(`Error al cargar los posts del usuario ${userId}:`, err);
        this.userPosts = []; 
      }
    });
  }

  onAvatarSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedAvatarFile = file;
      const reader = new FileReader();
      reader.onload = e => this.avatarPreviewUrl = reader.result;
      reader.readAsDataURL(file);
    } else {
      this.selectedAvatarFile = null;
      this.avatarPreviewUrl = null;
    }
     element.value = "";
  }

  onUploadAvatar(): void {
    if (!this.selectedAvatarFile) return;
    this.isUploadingAvatar = true;
    this.avatarPreviewUrl = null;
    this.profileService.uploadAvatar(this.selectedAvatarFile).subscribe({
      next: (response) => {
        console.log('Avatar subido:', response);
        this.isUploadingAvatar = false;
        this.selectedAvatarFile = null; 
        this.refresh$.next(); 
      },
      error: (err: HttpErrorResponse) => { 
        console.error('Error al subir avatar:', err);
        this.isUploadingAvatar = false;
        this.selectedAvatarFile = null; 
        alert(`Error al subir avatar: ${err.error?.message || err.statusText}`);
      }
    });
  }

  cancelAvatarChange(): void {
    this.selectedAvatarFile = null;
    this.avatarPreviewUrl = null;
  }

  onBannerSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const file = element.files?.[0];
    if (file) {
      this.selectedBannerFile = file;
      const reader = new FileReader();
      reader.onload = e => this.bannerPreviewUrl = reader.result;
      reader.readAsDataURL(file);
    } else {
      this.selectedBannerFile = null;
      this.bannerPreviewUrl = null;
    }
     element.value = "";
  }

  onUploadBanner(): void {
    if (!this.selectedBannerFile) return;
    this.isUploadingBanner = true;
    this.bannerPreviewUrl = null; 
    this.profileService.uploadBanner(this.selectedBannerFile).subscribe({
      next: (response) => {
        console.log('Banner subido:', response);
        this.isUploadingBanner = false;
        this.selectedBannerFile = null;
        this.refresh$.next();
      },
      error: (err: HttpErrorResponse) => { 
        console.error('Error al subir banner:', err);
        this.isUploadingBanner = false;
        this.selectedBannerFile = null; 
        if (err.status === 422 && err.error?.message?.includes('dimensions')) {
          alert('Error: La imagen del banner no cumple con las dimensiones requeridas (entre 300x100 y 3000x1000 píxeles).');
        } else {
          alert(`Error al subir banner: ${err.error?.message || err.statusText}`);
        }
      }
    });
  }

  cancelBannerChange(): void {
    this.selectedBannerFile = null;
    this.bannerPreviewUrl = null;
  }


   togglePostMenu(postId: number): void {
     this.openPostId = (this.openPostId === postId) ? null : postId;
   }

   onDeletePost(postId: number): void {
     if (confirm('¿Eliminar esta publicación?')) {
       this.postService.deletePost(postId).subscribe({
         next: () => {
           this.userPosts = this.userPosts.filter(post => post.id !== postId);
           this.openPostId = null; 
         },
         error: (err) => { console.error('Error al eliminar post:', err); this.openPostId = null; }
       });
     } else {
       this.openPostId = null; 
     }
   }

} 