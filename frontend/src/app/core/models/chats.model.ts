import { User } from './user.model';

export interface Message {
  id: number;
  content_messages: string;
  chat_id: number;
  user_id: number;
  created_at: string;
  user: User;
}

export interface Chat {
  id: number;
  users: User[];
  messages: Message[]; 
  created_at: string;
  updated_at: string;
}