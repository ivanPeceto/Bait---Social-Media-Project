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

export interface PaginatedLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface PaginatedMeta {
  current_page: number;
  from: number;
  last_page: number;
  links: { url: string | null; label: string; active: boolean }[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: PaginatedLinks;
  meta: PaginatedMeta;
}