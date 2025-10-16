import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { AuthService } from '../../features/auth/services/auth.service';
import { PostService, Post, PostComment } from '../../features/post/services/post.service';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    DatePipe,
    RouterLink
  ],
  templateUrl: './home.html',
})
export default class Home implements OnInit {
  private authService = inject(AuthService);
  private postService = inject(PostService);
  private fb = inject(FormBuilder);

  public currentUser: any | null = null;
  public posts: Post[] = [];
  public postForm: FormGroup;
  public apiErrors: any = {};

  constructor() {
    this.postForm = this.fb.group({
      content_posts: ['', [Validators.required, Validators.maxLength(280)]]
    });
    this.currentUser = this.authService.getCurrentUser();
    console.log('Usuario actual al cargar el componente:', this.currentUser);
  }

  ngOnInit(): void {
    this.loadPosts();
  }
  loadPosts(): void {
    this.postService.getPosts().subscribe({
      next: (response) => {
        this.posts = response || [];
        console.log('Posteos cargados desde el servidor:', this.posts);
      },
      error: (err) => {
        console.error('Error al cargar los posteos:', err);
        this.posts = [];
      }
    });
  }

  onPostSubmit(): void {
    this.apiErrors = {};
    if (this.postForm.invalid) { return; }
    const content = this.postForm.value.content_posts;

    this.postService.createPost(content).subscribe({
      next: (newPost) => {
        this.posts.unshift(newPost);
        this.postForm.reset();
      },
      error: (errorResponse) => {
        if (errorResponse.status === 422) {
          this.apiErrors = errorResponse.error.errors;
        }
      }
    });
  }

  onDeletePost(postId: number): void {
    if (confirm('¿Estás seguro de que quieres eliminar esta publicación?')) {
      this.postService.deletePost(postId).subscribe({
        next: () => {
          this.posts = this.posts.filter(post => post.id !== postId);
        },
        error: (err) => {
          console.error('Error al eliminar el post', err);
        }
      });
    }
  }
  
  logout(): void {
    this.authService.logout();
  }
}
