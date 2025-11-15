import { User } from './user.model';
import { MultimediaContent } from './multimedia-content.model';
import { UserReactionStatus } from './user-reaction-status.model';

export interface Post {
  id: number;
  content_posts: string;
  user: User;
  user_id: number;
  created_at: string;
  updated_at: string;
  reactions_count?: number;
  comments_count?: number;
  reposts_count?: number;
  is_liked_by_user?: boolean;
  user_reaction_status?: UserReactionStatus;
  multimedia_contents?: MultimediaContent[];
  type: 'post';
}

export interface Repost {
  id: number;
  created_at: string;
  user: User; 
  post: Post; 
  type: 'repost';
}