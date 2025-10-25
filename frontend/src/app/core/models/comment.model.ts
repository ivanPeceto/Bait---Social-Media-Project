import { User } from './user.model'; 

export interface Comment {
  id: number;
  content_comments: string; 
  user_id: number;
  post_id: number;
  user: User;
  created_at: string;
  updated_at: string;
}