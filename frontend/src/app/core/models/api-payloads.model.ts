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

export interface CreateReactionPayload {
  post_id: number;
  reaction_type_id: number;
}

export interface CreateRepostPayload {
  post_id: number;
}
export interface FollowPayload {
  following_id: number; 
}
export interface FollowUserInfo {
  id: number;
  name: string;
  username: string;
  avatar: { 
    id: number;
    url_avatars: string;
  } | null; 
  created_at: string;
  updated_at: string;
} 