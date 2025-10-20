import { Component, inject, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink, RouterOutlet } from '@angular/router';
import { CommonModule, DatePipe } from '@angular/common';
import { BehaviorSubject, Observable, EMPTY, of } from 'rxjs'; 
import { map, switchMap, tap, catchError } from 'rxjs/operators'; // Añadido catchError
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
  public userProfile$: Observable<User | null> = of(null); // Inicializado de forma segura
  public isOwnProfile = false;                           
  public userPosts: Post[] = [];                      
  public openPostId: number | null = null;          
  public currentUserId: number | null = null;     
  public isLoading = true; // Variable de carga añadida
  public error: string | null = null; // Manejo de error


  public selectedAvatarFile: File | null = null;       
  public selectedBannerFile: File | null = null;       
  public isUploadingAvatar = false;                    
  public isUploadingBanner = false;                     
  public avatarPreviewUrl: string | ArrayBuffer | null = null; 
  public bannerPreviewUrl: string | ArrayBuffer | null = null; 

  // private profileUserIdToLoad: string | null = null; // No usado en esta versión, mejor usar username/id.


  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    this.currentUserId = currentUser?.id ?? null;

    const profileLoader$ = this.refresh$.pipe(
      tap(() => {
        this.isLoading = true;
        this.error = null;
        // Limpieza de estado de subida/previsualización al cargar un nuevo perfil
        this.selectedAvatarFile = null;
        this.selectedBannerFile = null;
        this.avatarPreviewUrl = null;
        this.bannerPreviewUrl = null;
        this.isUploadingAvatar = false;
        this.isUploadingBanner = false;
      }),
      switchMap(() => this.route.paramMap),
      switchMap(params => {
        const idParam = params.get('id');
        const usernameParam = params.get('username');

        let profileIdentifier: string | number | null = null;
        let isOwn = false;

        // 1. Determinar el perfil a cargar
        if (idParam) {
            profileIdentifier = idParam;
            isOwn = this.currentUserId?.toString() === idParam;
        } else if (usernameParam) {
            // Asumiendo que getUserProfile puede aceptar ID o Username, 
            // y que getPublicProfile usa Username (según el código original del conflicto)
            profileIdentifier = usernameParam;
            isOwn = currentUser?.username === usernameParam;
        } else if (this.currentUserId) {
            // Si no hay parámetros, cargamos el perfil propio
            profileIdentifier = this.currentUserId;
            isOwn = true;
        } else {
            console.error("Usuario no logueado y sin parámetros de perfil.");
            return of(null);
        }

        this.isOwnProfile = isOwn;
        
        // 2. Llamada al servicio
        if (isOwn) {
            return this.profileService.getOwnProfile();
        } else if (typeof profileIdentifier === 'string') {
             // Si es un username (o ID como string), usamos la función de perfil público
             return this.profileService.getUserProfile(profileIdentifier).pipe( 
                 catchError(error => {
                     console.error('Error al cargar perfil público:', error);
                     this.error = 'No se pudo cargar el perfil público. El usuario podría no existir.';
                     return of(null); // Retorna un Observable de null en caso de error
                 })
             );
        }
        return of(null);
      })
    );


    this.userProfile$ = profileLoader$.pipe(
        map((response: any) => response ? response.data as User : null),
        tap((user: User | null) => { 
          this.isLoading = false;
          if (user && user.id) {
            console.log("Perfil cargado correctamente:", user); 
            // Solo cargamos posts si el perfil es válido
            this.loadUserPosts(user.id.toString());
          } else {
            this.userPosts = [];
            if (!this.error) {
                 this.error = 'No se pudo cargar el perfil o el usuario no existe.';
            }
          }
        }),
        catchError(err => {
            this.isLoading = false;
            this.error = 'Error de conexión al cargar el perfil.';
            return of(null);
        })
    );
  }


  // ... (El resto de tus métodos son correctos y se mantienen) ...
  // loadUserPosts, onAvatarSelected, onUploadAvatar, cancelAvatarChange,
  // onBannerSelected, onUploadBanner, cancelBannerChange, togglePostMenu, onDeletePost

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