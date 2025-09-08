export interface User {
  id: number;
  name: string;
  username?: string;
  email?: string;
  photo_url?: string;
  role: 'user' | 'admin';
  total_bonuses?: number;
  average_rating?: number;
  created_at: string;
}

export interface CatchRecord {
  id: number;
  user: User;
  lat: number;
  lng: number;
  species?: string;
  length?: number;
  weight?: number;
  style?: string;
  lure?: string;
  tackle?: string;
  notes?: string;
  photo_url?: string;
  privacy: 'all' | 'friends' | 'me';
  caught_at?: string;
  likes_count: number;
  comments_count: number;
  liked_by_me?: boolean;
  created_at: string;
}

export interface CatchComment {
  id: number;
  body: string;
  user: User;
  created_at: string;
}

export interface Point {
  id: number;
  user: User;
  lat: number;
  lng: number;
  title: string;
  description?: string;
  cover_url?: string;
  privacy: 'all' | 'friends' | 'me';
  media_count?: number;
  media?: PointMedia[];
  created_at: string;
}

export interface PointMedia {
  id: number;
  url: string;
  created_at: string;
}

export interface WeatherFav {
  id: number;
  lat: number;
  lng: number;
  label: string;
  created_at: string;
}

export interface Banner {
  id: number;
  slot: string;
  image_url: string;
  click_url: string;
  is_active: boolean;
  start_at?: string;
  end_at?: string;
  created_at: string;
}

export interface Rating {
  id: number;
  entity_type: 'catch' | 'point' | 'user';
  entity_id: number;
  user_id: number;
  value: number;
  created_at: string;
}

export interface Bonus {
  id: number;
  action: 'add_catch' | 'add_point' | 'like_received' | 'comment_received' | 'daily_login';
  amount: number;
  meta?: any;
  created_at: string;
}

export interface Notification {
  id: number;
  type: string;
  title: string;
  body?: string;
  is_read: boolean;
  read_at?: string;
  created_at: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  email: string;
  password: string;
  name: string;
  username?: string;
  consents: {
    personalData: boolean;
    rules: boolean;
    offer: boolean;
  };
}

export interface AddCatchRequest {
  lat: number;
  lng: number;
  species?: string;
  length?: number;
  weight?: number;
  style?: string;
  lure?: string;
  tackle?: string;
  notes?: string;
  photo_url?: string;
  privacy?: 'all' | 'friends' | 'me';
  caught_at?: string;
}

export interface AddPointRequest {
  lat: number;
  lng: number;
  title: string;
  description?: string;
  cover_url?: string;
  privacy?: 'all' | 'friends' | 'me';
  media?: string[];
}

export interface AddCommentRequest {
  body: string;
}

export interface SaveWeatherFavRequest {
  lat: number;
  lng: number;
  label: string;
}

export interface AddRatingRequest {
  entity_type: 'catch' | 'point' | 'user';
  entity_id: number;
  value: number;
}

// Social Features
export interface Group {
  id: number;
  name: string;
  description?: string;
  cover_url?: string;
  privacy: 'public' | 'private' | 'closed';
  owner_id: number;
  owner?: User;
  members_count: number;
  members?: User[];
  created_at: string;
}

export interface Event {
  id: number;
  title: string;
  description?: string;
  lat?: number;
  lng?: number;
  location_name?: string;
  start_at: string;
  end_at?: string;
  max_participants?: number;
  status: 'draft' | 'published' | 'cancelled' | 'completed';
  organizer_id: number;
  organizer?: User;
  group_id?: number;
  group?: Group;
  cover_url?: string;
  participants_count?: number;
  participants?: User[];
  created_at: string;
}

export interface Chat {
  id: number;
  name?: string;
  type: 'private' | 'group' | 'event';
  group_id?: number;
  group?: Group;
  event_id?: number;
  event?: Event;
  latest_message?: ChatMessage;
  created_at: string;
}

export interface ChatMessage {
  id: number;
  chat_id: number;
  user_id: number;
  user?: User;
  message: string;
  attachment_url?: string;
  attachment_type?: 'image' | 'file' | 'location';
  is_read: boolean;
  created_at: string;
}

export interface LiveSession {
  id: number;
  title: string;
  description?: string;
  lat: number;
  lng: number;
  stream_url?: string;
  status: 'scheduled' | 'live' | 'ended';
  user_id: number;
  user?: User;
  event_id?: number;
  event?: Event;
  viewers_count: number;
  started_at?: string;
  ended_at?: string;
  created_at: string;
}
