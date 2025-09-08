export interface User {
  id: number;
  name: string;
  username?: string;
  email?: string;
  photo_url?: string;
  role: 'user' | 'pro' | 'premium' | 'admin';
  is_premium?: boolean;
  premium_expires_at?: string;
  crown_icon_url?: string;
  bonus_balance?: number;
  last_bonus_earned_at?: string;
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

// Subscription types
export interface Subscription {
  id: number;
  user_id: number;
  type: 'pro' | 'premium';
  status: 'active' | 'expired' | 'cancelled';
  payment_method?: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses';
  amount?: number;
  bonus_amount?: number;
  starts_at?: string;
  expires_at?: string;
  cancelled_at?: string;
  cancellation_reason?: string;
  metadata?: Record<string, any>;
  created_at: string;
  updated_at: string;
  user?: User;
  payments?: Payment[];
}

export interface Payment {
  id: number;
  user_id: number;
  subscription_id?: number;
  payment_id: string;
  provider: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses';
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled' | 'refunded';
  type: 'subscription_pro' | 'subscription_premium' | 'bonus_purchase';
  amount: number;
  currency: string;
  bonus_amount?: number;
  description?: string;
  provider_data?: Record<string, any>;
  metadata?: Record<string, any>;
  paid_at?: string;
  expires_at?: string;
  created_at: string;
  updated_at: string;
  user?: User;
  subscription?: Subscription;
}

export interface SubscriptionPlan {
  name: string;
  price_rub: number;
  price_bonus: number;
  duration_days: number;
  features: Record<string, boolean>;
  description: string;
  crown_icon_url?: string;
}

export interface PaymentMethod {
  id: string;
  name: string;
  icon: string;
}

export interface SubscriptionStatus {
  is_pro: boolean;
  is_premium: boolean;
  crown_icon_url?: string;
  bonus_balance: number;
  active_subscriptions: Subscription[];
  role: string;
}

export interface SubscriptionPlansResponse {
  plans: {
    pro: SubscriptionPlan;
    premium: SubscriptionPlan;
  };
  payment_methods: PaymentMethod[];
  user_bonus_balance: number;
}
