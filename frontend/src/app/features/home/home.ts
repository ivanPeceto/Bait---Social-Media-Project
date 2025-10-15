import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { AuthService } from '../../features/auth/services/auth.service';
import { PostService, Post, PostComment } from '../../features/post/services/post.service';
import { CommentService } from '../post/services/comment.service';
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
  private commentService = inject(CommentService);

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
      next: (postsResponse: any) => {
        const postsFromApi = postsResponse || [];
        
        postsFromApi.forEach((post: Post) => {
          post.comments = [];
          this.commentService.getCommentsForPost(post.id).subscribe({
            next: (commentsResponse: any) => {
              post.comments = commentsResponse.data || [];
            }
          });
        });

        this.posts = postsFromApi;
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


onCommentSubmit(postId: number, content: string): void {
  if (!content || !content.trim()) {
    return;
  }

  this.commentService.createComment(postId, content).subscribe({
    next: (newCommentResponse: any) => {
      const newCommentData = newCommentResponse.data;

      // Creamos el objeto localmente para una respuesta instantánea
      const newComment: PostComment = {
        id: newCommentData.id,
        content_comments: newCommentData.content_comments,
        created_at: newCommentData.created_at,
        user: this.currentUser, // Usamos el usuario actual
        post_id: newCommentData.post_id,
        user_id: this.currentUser.id,
      };

      const post = this.posts.find(p => p.id === postId);
      if (post) {
        post.comments.push(newComment); // Añadimos el comentario a la lista
      }
    },
    error: (err) => console.error('Error detallado al crear el comentario:', err)
  });
}
  logout(): void {
    this.authService.logout();
  }
}
