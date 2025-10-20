// en src/app/features/home/home.ts (CÓDIGO COMPLETO Y FINAL)

import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
<<<<<<< HEAD
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http'; 
import { AuthService } from '../../features/auth/services/auth.service';
import { PostService, Post } from '../../features/post/services/post.service'; 
import { RouterLink } from '@angular/router';
=======
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, FormControl } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { debounceTime, distinctUntilChanged, switchMap, tap, of } from 'rxjs';

import { AuthService } from '../../features/auth/services/auth.service';
import { PostService, Post } from '../../features/post/services/post.service';
import { SearchService, UserSearchResult } from '../search/services/search.service';
>>>>>>> origin/feature/frontend/search

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [ CommonModule, ReactiveFormsModule, DatePipe, RouterLink ],
  templateUrl: './home.html',
})
export default class Home implements OnInit {
  // --- Inyección de Servicios ---
  private authService = inject(AuthService);
  private postService = inject(PostService);
  private searchService = inject(SearchService);
  private router = inject(Router);
  private fb = inject(FormBuilder);

  // --- Propiedades para Posts y Usuario ---
  public currentUser: any | null = null;
  public posts: Post[] = [];
  public postForm: FormGroup;
  public apiErrors: any = {};
  public openPostId: number | null = null; 

  // --- Propiedades para la Búsqueda ---
  public searchControl = new FormControl('');
  public searchResults: UserSearchResult[] = [];
  public isSearching = false;
  public showResults = false;

  constructor() {
    this.postForm = this.fb.group({
      content_posts: ['', [Validators.required, Validators.maxLength(280)]]
    });
<<<<<<< HEAD
    this.currentUser = this.authService.getCurrentUser();
=======
>>>>>>> origin/feature/frontend/search
  }

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadPosts();
    this.setupSearch(); // Inicializamos la lógica de búsqueda
  }

<<<<<<< HEAD
  loadPosts(): void {
    this.postService.getPosts().subscribe({ 
      next: (response) => { this.posts = response || []; },
      error: (err) => { console.error('Error al cargar posts:', err); this.posts = []; }
=======
  // --- Lógica de Búsqueda ---

  setupSearch(): void {
    this.searchControl.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      tap(() => {
        if (this.searchControl.value && this.searchControl.value.trim().length > 0) {
            this.isSearching = true;
            this.showResults = true;
        } else {
            this.showResults = false; // Oculta si el input está vacío
        }
      }),
      switchMap(term => {
        if (!term || term.trim().length < 2) {
          return of([]); // No busques si el término es muy corto
        }
        if (term.startsWith('@')) {
          const username = term.substring(1);
          return this.searchService.searchByUsername(username);
        } else {
          return this.searchService.searchByName(term);
        }
      })
    ).subscribe(users => {
      this.isSearching = false;
      this.searchResults = users;
    });
  }

  /**
   * Navega al perfil del usuario y limpia la búsqueda.
   * ESTA ES LA FUNCIÓN QUE HACE EL CLIC FUNCIONAR.
   */
  goToProfile(username: string): void {
    this.router.navigate(['/profile', username]);
    this.clearSearch();
  }

  clearSearch(): void {
    this.showResults = false;
    this.searchResults = [];
    this.searchControl.setValue('', { emitEvent: false }); // Limpia el input sin disparar una nueva búsqueda
  }

  // --- Lógica de Posts y Sesión ---

  loadPosts(): void {
    this.postService.getPosts().subscribe({
      next: (response) => this.posts = response || [],
      error: (err) => console.error('Error al cargar los posteos:', err)
>>>>>>> origin/feature/frontend/search
    });
  }

  onPostSubmit(): void {
    this.apiErrors = {};
<<<<<<< HEAD
    if (this.postForm.invalid) {
        this.postForm.markAllAsTouched(); 
        return;
    }

    const content = this.postForm.value.content_posts;

    this.postService.createPost(content).subscribe({ 
      next: (newPost: Post) => {
=======
    if (this.postForm.invalid) { return; }
    
    this.postService.createPost(this.postForm.value.content_posts).subscribe({
      next: (newPost) => {
>>>>>>> origin/feature/frontend/search
        this.posts.unshift(newPost);
        this.postForm.reset();
      },
      error: (errorResponse: HttpErrorResponse) => {
        console.error("Error al crear post:", errorResponse);
        if (errorResponse.status === 422 && errorResponse.error?.errors) {
          this.apiErrors = errorResponse.error.errors;
        } else {
          this.apiErrors = { general: ['Ocurrió un error inesperado al postear.'] };
           alert('Ocurrió un error al postear. Intenta de nuevo.');
        }
      }
    });
  }

<<<<<<< HEAD
   togglePostMenu(postId: number): void {
     this.openPostId = (this.openPostId === postId) ? null : postId;
   }

   onDeletePost(postId: number): void {
     if (confirm('¿Eliminar esta publicación?')) {
       this.postService.deletePost(postId).subscribe({ 
         next: () => {
           this.posts = this.posts.filter(post => post.id !== postId);
           this.openPostId = null;
         },
         error: (err) => { console.error('Error al eliminar post:', err); this.openPostId = null; }
       });
     } else {
       this.openPostId = null;
     }
   }

} 
=======
  onDeletePost(postId: number): void {
    if (confirm('¿Estás seguro de que quieres eliminar esta publicación?')) {
      this.postService.deletePost(postId).subscribe({
        next: () => this.posts = this.posts.filter(post => post.id !== postId),
        error: (err) => console.error('Error al eliminar el post', err)
      });
    }
  }

  isPrivilegedUser(): boolean {
    if (!this.currentUser || !this.currentUser.role) return false;
    return this.currentUser.role === 'admin' || this.currentUser.role === 'moderator';
  }
  
  logout(): void {
    this.authService.logout();
  }
}
>>>>>>> origin/feature/frontend/search
