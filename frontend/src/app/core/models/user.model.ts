export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    role: any;
    state: any;
    avatar?: {
        id?: number;
        url?: string;
        url_avatars?: string; 
    }| null;
    banner?: {
        id?: number;
        url?: string;
        url_banners?: string;
    }| null;
    created_at?: string;

}