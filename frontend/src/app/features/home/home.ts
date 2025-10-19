import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http'; 
import { AuthService } from '../../features/auth/services/auth.service';
import { PostService, Post } from '../../features/post/services/post.service'; 
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [ CommonModule, ReactiveFormsModule, DatePipe, RouterLink ],
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
  public openPostId: number | null = null; 

  constructor() {
    this.postForm = this.fb.group({
      content_posts: ['', [Validators.required, Validators.maxLength(280)]]
    });
    this.currentUser = this.authService.getCurrentUser();
  }

  ngOnInit(): void {
    this.loadPosts();
  }

  loadPosts(): void {
    this.postService.getPosts().subscribe({ 
      next: (response) => { this.posts = response || []; },
      error: (err) => { console.error('Error al cargar posts:', err); this.posts = []; }
    });
  }

  onPostSubmit(): void {
    this.apiErrors = {};
    if (this.postForm.invalid) {
        this.postForm.markAllAsTouched(); 
        return;
    }

    const content = this.postForm.value.content_posts;

    this.postService.createPost(content).subscribe({ 
      next: (newPost: Post) => {
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