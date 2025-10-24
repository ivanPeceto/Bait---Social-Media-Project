import { User } from './user.model';
import { MultimediaContent } from './multimedia-content.model';

export interface Post {
  id: number;
  content_posts: string;
  user: User;
  user_id: number; 
  comments?: any[];
  reposts?: any[];
  reactions?: any[];
  created_at: string;
  updated_at: string;
  is_liked_by_user?: boolean;
  multimedia_contents?: MultimediaContent[];
}