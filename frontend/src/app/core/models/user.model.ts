export interface Avatar {
  id: number;
  url_avatars: string; 
}

export interface Banner {
  id: number;
  url_banners: string;
}

export interface User {
  id: number;
  username: string;
  name: string;
  email: string;
  role?: string;
  state?: string;
  avatar?: Avatar | null; 
  banner?: Banner | null;
  
  created_at: string;
  updated_at: string;
}