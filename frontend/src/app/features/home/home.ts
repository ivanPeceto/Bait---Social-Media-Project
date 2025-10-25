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
import { PostService } from '../../features/post/services/post.service';
import { Post } from '../../core/models/post.model';
import { SearchService, UserSearchResult } from '../search/services/search.service';
import { PostCommentsModalComponent } from '../comments/components/post-comments-modal/post-comments-modal.component'; 
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, DatePipe, RouterLink, PostCommentsModalComponent],
  templateUrl: './home.html',
})
export default class Home implements OnInit {
  private authService = inject(AuthService);
  private postService = inject(PostService);
  private searchService = inject(SearchService);
  private router = inject(Router);
  private fb = inject(FormBuilder);

  public currentUser: any | null = null;
  public posts: Post[] = [];
  public postForm: FormGroup;
  public apiErrors: any = {};
  public openPostId: number | null = null;

  public searchControl = new FormControl('');
  public searchResults: UserSearchResult[] = [];
  public isSearching = false;
  public showResults = false;
  public isCommentModalOpen = false;
  public selectedPostForModal: Post | null = null;
  public isLoading = false;
  public isCurrentUserAdminOrMod: boolean = false;

  constructor() {
    this.postForm = this.fb.group({
      content_posts: ['', [Validators.required, Validators.maxLength(280)]],
    });
  }

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.isCurrentUserAdminOrMod = !!this.currentUser &&
                                  (this.currentUser.role?.toLowerCase() === 'admin' ||
                                   this.currentUser.role?.toLowerCase() === 'moderator');
    this.loadPosts();
    this.setupSearch();
  }

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
  }

  loadPosts(): void {
    this.isLoading = true;

    this.postService.getPosts().subscribe({
      next: (response) => {
        this.posts = response || [];
        this.isLoading = false;
      },
      error: (err) => {
        this.isLoading = false; 
      },
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
      // Determina qué método del servicio llamar basado en el rol
      const deleteCall = this.isCurrentUserAdminOrMod
                         ? this.postService.deletePostAsAdmin(postId)
                         : this.postService.deletePost(postId); 

      deleteCall.subscribe({
        next: () => {
          this.posts = this.posts.filter((post) => post.id !== postId);
          console.log('Post eliminado con éxito');
        },
        error: (err) => {
          console.error('Error al eliminar el post:', err);
          alert(`Error al eliminar: ${err.error?.message || err.message}`);
        }
      });
    }
    this.openPostId = null; 
  }

  openCommentModal(post: Post): void {
    this.selectedPostForModal = post;
    this.isCommentModalOpen = true;
  }
}
