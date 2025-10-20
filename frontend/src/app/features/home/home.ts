// en src/app/features/home/home.ts

import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  Validators,
  ReactiveFormsModule,
  FormControl,
} from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { debounceTime, distinctUntilChanged, switchMap, tap, of } from 'rxjs';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../../features/auth/services/auth.service';
import { PostService, Post } from '../../features/post/services/post.service';
import { SearchService, UserSearchResult } from '../search/services/search.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, DatePipe, RouterLink],
  templateUrl: './home.html',
})
export default class Home implements OnInit {
  // --- Inyección de Servicios ---
  private authService = inject(AuthService); // Se usa para currentUser
  private postService = inject(PostService);
  private searchService = inject(SearchService);
  private router = inject(Router);
  private fb = inject(FormBuilder); // --- Propiedades para Posts y Usuario ---

  public currentUser: any | null = null; // Mantenemos aquí para el formulario y el feed
  public posts: Post[] = [];
  public postForm: FormGroup;
  public apiErrors: any = {};
  public openPostId: number | null = null; // Añadido para el menú // --- Propiedades para la Búsqueda ---

  public searchControl = new FormControl('');
  public searchResults: UserSearchResult[] = [];
  public isSearching = false;
  public showResults = false;

  constructor() {
    this.postForm = this.fb.group({
      content_posts: ['', [Validators.required, Validators.maxLength(280)]],
    });
  }

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadPosts();
    this.setupSearch(); // Inicializamos la lógica de búsqueda
  }

  // --- Lógica de Búsqueda ---
  setupSearch(): void {
    this.searchControl.valueChanges
      .pipe(
        debounceTime(300),
        distinctUntilChanged(),
        tap((term) => {
          if (term && term.trim().length > 0) {
            this.isSearching = true;
            this.showResults = true;
          } else {
            this.showResults = false;
          }
        }),
        switchMap((term) => {
          if (!term || term.trim().length < 2) {
            this.isSearching = false;
            return of([]);
          }
          if (term.startsWith('@')) {
            const username = term.substring(1);
            return this.searchService.searchByUsername(username);
          } else {
            return this.searchService.searchByName(term);
          }
        })
      )
      .subscribe((users) => {
        this.isSearching = false;
        this.searchResults = users;
      });
  }

  goToProfile(username: string): void {
    this.router.navigate(['/profile', username]);
    this.clearSearch();
  }

  clearSearch(): void {
    this.showResults = false;
    this.searchResults = [];
    this.searchControl.setValue('', { emitEvent: false });
  } // --- Lógica de Posts ---

  loadPosts(): void {
    this.postService.getPosts().subscribe({
      next: (response) => (this.posts = response || []),
      error: (err) => console.error('Error al cargar los posteos:', err),
    });
  }

  onPostSubmit(): void {
    this.apiErrors = {};
    if (this.postForm.invalid) {
      this.postForm.markAllAsTouched();
      return;
    }
    this.postService.createPost(this.postForm.value.content_posts).subscribe({
      next: (newPost) => {
        this.posts.unshift(newPost);
        this.postForm.reset();
      },
      error: (errorResponse: HttpErrorResponse) => {
        if (errorResponse.status === 422) {
          this.apiErrors = errorResponse.error.errors;
        } else {
          this.apiErrors = { general: ['Ocurrió un error inesperado al postear.'] };
        }
      },
    });
  }

  togglePostMenu(postId: number): void {
    this.openPostId = this.openPostId === postId ? null : postId;
  }

  onDeletePost(postId: number): void {
    if (confirm('¿Estás seguro de que quieres eliminar esta publicación?')) {
      this.postService.deletePost(postId).subscribe({
        next: () => (this.posts = this.posts.filter((post) => post.id !== postId)),
        error: (err) => console.error('Error al eliminar el post', err),
      });
    }
  }
}
