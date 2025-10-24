export interface CreatePostPayload {
  content_posts: string;
}

export interface UpdatePostPayload {
  content_posts: string;
}

export interface CreateCommentPayload {
  content_comments: string;
  post_id: number;
}

export interface UpdateCommentPayload {
  content_comments: string;
}