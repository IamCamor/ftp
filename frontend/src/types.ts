export interface User {
  id: number;
  name: string;
  username?: string;
  email?: string;
  photo_url?: string;
  role: 'user' | 'pro' | 'premium' | 'admin' | 'guide';
  is_premium?: boolean;
  premium_expires_at?: string;
  crown_icon_url?: string;
  bonus_balance?: number;
  last_bonus_earned_at?: string;
  followers_count?: number;
  following_count?: number;
  total_likes_received?: number;
  last_seen_at?: string;
  is_online?: boolean;
  total_bonuses?: number;
  average_rating?: number;
  bio?: string;
  location?: string;
  website?: string;
  // Поля для гида
  is_guide?: boolean;
  guide_icon_url?: string;
  guide_info?: string;
  guide_website?: string;
  guide_social_links?: {
    instagram?: string;
    vk?: string;
    telegram?: string;
    youtube?: string;
  };
  guide_rating?: number;
  guide_reviews_count?: number;
  // Настройки приватности
  privacy_settings?: {
    allow_friend_requests?: boolean;
    allow_follow_notifications?: boolean;
    show_online_status?: boolean;
    show_last_seen?: boolean;
  };
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
  group_id?: number;
  cover_url?: string;
  type?: string;
  organizer?: User;
  group?: any;
  participants?: User[];
  participants_count?: number;
  is_participant?: boolean;
  is_organizer?: boolean;
  created_at: string;
  updated_at: string;
}

export interface GroupPost {
  id: number;
  group_id: number;
  author_id: number;
  title: string;
  content?: string;
  images?: Array<{ id: number; url: string; created_at: string }>;
  location?: string;
  likes_count?: number;
  comments_count?: number;
  views_count?: number;
  is_liked?: boolean;
  author: User;
  group?: any;
  created_at: string;
  updated_at: string;
}

export interface CatchRecord {
  id: number;
  type?: 'track' | 'catch'; // Add type field to distinguish between tracks and individual catches
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
  photos?: string[];
  videos?: string[];
  main_photo?: string;
  main_video?: string;
  media_count?: number;
  photo_url?: string; // Legacy field
  additional_photos?: string; // JSON string of additional photos
  privacy: 'all' | 'friends' | 'me';
  caught_at?: string;
  likes_count: number;
  comments_count: number;
  liked_by_me?: boolean;
  point?: {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
  };
  fish_species?: {
    id: number;
    name: string;
    slug: string;
  };
  fishing_method?: {
    id: number;
    name: string;
    slug: string;
  };
  fishing_location?: {
    id: number;
    name: string;
    slug: string;
  };
  weather?: {
    temperature?: string;
    pressure?: number;
    wind_speed?: number;
    cloudiness?: string;
    precipitation?: string;
    wind_direction?: string;
  };
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
  catches_count?: number;
  species?: string;
  weather_data?: any;
  // Параметры места
  is_paid_place?: boolean;
  has_security?: boolean;
  has_parking?: boolean;
  // Время работы
  working_hours?: {
    is_24_7?: boolean;
    schedule?: {
      monday?: { open: string; close: string; closed?: boolean };
      tuesday?: { open: string; close: string; closed?: boolean };
      wednesday?: { open: string; close: string; closed?: boolean };
      thursday?: { open: string; close: string; closed?: boolean };
      friday?: { open: string; close: string; closed?: boolean };
      saturday?: { open: string; close: string; closed?: boolean };
      sunday?: { open: string; close: string; closed?: boolean };
    };
  };
  // Дополнительная информация о месте
  place_type?: 'lake' | 'river' | 'pond' | 'sea' | 'reservoir' | 'other' | 'fishing_spot' | 'resort' | 'slip' | 'marina';
  depth?: number;
  water_quality?: 'excellent' | 'good' | 'average' | 'poor';
  accessibility?: 'easy' | 'medium' | 'hard';
  facilities?: string[];
  created_at: string;
  type?: 'point' | 'catch';
  fish_species?: {
    id: number;
    name: string;
    scientific_name: string;
  };
  length?: number;
  weight?: number;
  caught_at?: string;
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

export interface AppNotification {
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
  // Параметры места
  is_paid_place?: boolean;
  has_security?: boolean;
  has_parking?: boolean;
  // Поля погоды
  temperature?: number;
  pressure?: number;
  wind_speed?: number;
  cloudiness?: string;
  precipitation?: string;
  wind_direction?: string;
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

export interface WeatherPoint {
  id: number;
  user_id: number;
  name: string;
  lat: number;
  lng: number;
  city?: string;
  country?: string;
  created_at: string;
  updated_at: string;
}

export interface AddWeatherPointRequest {
  name: string;
  lat: number;
  lng: number;
  city?: string;
  country?: string;
}

export interface UpdateWeatherPointRequest {
  name?: string;
  lat?: number;
  lng?: number;
  city?: string;
  country?: string;
}

export interface WeatherData {
  temperature: number;
  pressure: number;
  wind_speed: number;
  cloudiness: string;
  precipitation: string;
  wind_direction: string;
  humidity?: number;
  description?: string;
  icon?: string;
}

export interface TestData {
  users: User[];
  points: Point[];
  catches: CatchRecord[];
  events: Event[];
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

export interface AppEvent {
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
  type: 'pro' | 'premium' | 'guide';
  status: 'active' | 'expired' | 'cancelled';
  payment_method?: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses' | 'tinkoff_pay';
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
  provider: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses' | 'tinkoff_pay';
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled' | 'refunded';
  type: 'subscription_pro' | 'subscription_premium' | 'subscription_guide' | 'bonus_purchase';
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

export interface GuideSubscriptionPlan {
  name: string;
  price_rub: number;
  duration_days: number;
  features: Record<string, boolean>;
  description: string;
  icon_url?: string;
}

export interface PaymentMethod {
  id: string;
  name: string;
  icon: string;
}

export interface SubscriptionStatus {
  is_pro: boolean;
  is_premium: boolean;
  is_guide: boolean;
  crown_icon_url?: string;
  guide_icon_url?: string;
  bonus_balance: number;
  active_subscriptions: Subscription[];
  role: string;
}

export interface SubscriptionPlansResponse {
  plans: {
    pro: SubscriptionPlan;
    premium: SubscriptionPlan;
    guide: GuideSubscriptionPlan;
  };
  payment_methods: PaymentMethod[];
  user_bonus_balance: number;
}

// Follow types
export interface FollowResponse {
  success: boolean;
  message: string;
  data: {
    following: boolean;
    followers_count: number;
  };
}

export interface FollowersResponse {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

// Online status types
export interface OnlineStatusResponse {
  success: boolean;
  data: {
    is_online: boolean;
    last_seen_at: string;
  };
}

export interface OnlineUsersResponse {
  success: boolean;
  data: User[];
}

// Media limits types
export interface MediaLimits {
  max_photos: number;
  max_videos: number;
  max_media_total: number;
  video_enabled: boolean;
}

// Track types
export interface TrackPoint {
  lat: number;
  lng: number;
  timestamp: string;
  smartwatch_data?: {
    heart_rate: number;
    steps: number;
    calories: number;
  };
}

export interface Track {
  id: number;
  name?: string;
  title?: string;
  description?: string;
  status: 'active' | 'paused' | 'completed';
  started_at: string;
  ended_at?: string;
  duration_minutes?: number;
  total_distance?: number;
  total_catches: number;
  total_weight?: number;
  average_weight?: number;
  track_points: TrackPoint[];
  user: {
    id: number;
    name: string;
    username: string;
    avatar_url?: string;
  };
  place?: {
    id: number;
    name: string;
  };
  smartwatch_data?: {
    heart_rate: number[];
    steps: number;
    calories: number;
    sleep_quality: string;
  };
  catches: CatchRecord[];
  created_at: string;
  updated_at: string;
}

export interface CreateTrackRequest {
  title?: string;
  description?: string;
  lat: number;
  lng: number;
  smartwatch_data?: any;
}

export interface UpdateTrackRequest {
  action: 'add_point' | 'complete' | 'pause' | 'resume';
  lat?: number;
  lng?: number;
  smartwatch_data?: any;
}

export interface AddCatchToTrackRequest {
  species: string;
  weight: number;
  length: number;
  style?: string;
  lure?: string;
  tackle?: string;
  notes?: string;
  lat?: number;
  lng?: number;
}

// Promo codes and invites
export interface PromoCode {
  id: number;
  code: string;
  type: 'percentage' | 'fixed_amount';
  value: number;
  subscription_types: string[];
  max_uses?: number;
  used_count: number;
  is_active: boolean;
  valid_from?: string;
  valid_until?: string;
  created_by: number;
  created_at: string;
  updated_at: string;
}

export interface InviteCode {
  id: number;
  code: string;
  inviter_id: number;
  inviter?: User;
  discount_percentage: number;
  bonus_amount: number;
  max_uses?: number;
  used_count: number;
  is_active: boolean;
  valid_until?: string;
  created_at: string;
  updated_at: string;
}

export interface InviteUsage {
  id: number;
  invite_code_id: number;
  inviter_id: number;
  invitee_id: number;
  invitee?: User;
  subscription_type: string;
  discount_applied: number;
  bonus_earned: number;
  created_at: string;
}

export interface AppSettings {
  id: number;
  key: string;
  value: string;
  description?: string;
  created_at: string;
  updated_at: string;
}

export interface PromoCodeUsage {
  id: number;
  promo_code_id: number;
  user_id: number;
  user?: User;
  subscription_id: number;
  subscription?: Subscription;
  discount_applied: number;
  created_at: string;
}

export interface CreatePromoCodeRequest {
  code: string;
  type: 'percentage' | 'fixed_amount';
  value: number;
  subscription_types: string[];
  max_uses?: number;
  valid_from?: string;
  valid_until?: string;
}

export interface CreateInviteCodeRequest {
  discount_percentage: number;
  bonus_amount: number;
  max_uses?: number;
  valid_until?: string;
}

export interface ApplyPromoCodeRequest {
  code: string;
  subscription_type: string;
}

export interface ApplyInviteCodeRequest {
  code: string;
  subscription_type: string;
}

// Search types
export interface SearchResult {
  type: 'catch' | 'event' | 'track' | 'user' | 'point' | 'fish_species' | 'fishing_method' | 'bait' | 'location';
  id: number;
  title: string;
  description?: string;
  image?: string;
  user?: User;
  created_at: string;
  relevance_score?: number;
}

export interface SearchFilters {
  type?: 'catch' | 'event' | 'track' | 'user' | 'point' | 'fish_species' | 'fishing_method' | 'bait' | 'location';
  date_from?: string;
  date_to?: string;
  location?: {
    lat: number;
    lng: number;
    radius: number; // в километрах
  };
  species?: string[];
  event_status?: 'draft' | 'published' | 'cancelled' | 'completed';
  user_role?: 'user' | 'pro' | 'premium' | 'admin' | 'guide';
  sort_by?: 'relevance' | 'date' | 'popularity' | 'rating';
  sort_order?: 'asc' | 'desc';
}

export interface SearchRequest {
  query: string;
  filters?: SearchFilters;
  page?: number;
  per_page?: number;
}

export interface SearchResponse {
  results: SearchResult[];
  total: number;
  page: number;
  per_page: number;
  last_page: number;
  aggregations?: {
    types: Record<string, number>;
    species: Record<string, number>;
    locations: Record<string, number>;
  };
}

// Reference types
export interface FishSpecies {
  id: number;
  name: string;
  scientific_name?: string;
  description?: string;
  image_url?: string;
  habitat?: string;
  size_range?: string;
  weight_range?: string;
  season?: string;
  bait?: string[];
  fishing_methods?: string[];
  regulations?: string;
  conservation_status?: string;
  created_at: string;
}

export interface FishingMethod {
  id: number;
  name: string;
  description?: string;
  image_url?: string;
  equipment?: string[];
  techniques?: string[];
  best_season?: string;
  difficulty_level?: 'beginner' | 'intermediate' | 'advanced';
  tips?: string[];
  created_at: string;
}

export interface Bait {
  id: number;
  name: string;
  type: 'natural' | 'artificial' | 'live';
  description?: string;
  image_url?: string;
  target_species?: string[];
  best_season?: string;
  preparation?: string;
  storage_tips?: string;
  effectiveness_rating?: number;
  created_at: string;
}

export interface Location {
  id: number;
  name: string;
  type: 'lake' | 'river' | 'sea' | 'pond' | 'reservoir';
  description?: string;
  image_url?: string;
  coordinates?: {
    lat: number;
    lng: number;
  };
  region?: string;
  country?: string;
  fish_species?: string[];
  facilities?: string[];
  regulations?: string;
  best_season?: string;
  access_info?: string;
  created_at: string;
}

export interface ReferenceSearchResult {
  type: 'fish_species' | 'fishing_method' | 'bait' | 'location';
  id: number;
  title: string;
  description?: string;
  image?: string;
  category?: string;
  created_at: string;
  data: FishSpecies | FishingMethod | Bait | Location;
}

// Friends System
export interface Friendship {
  id: number;
  user_id: number;
  friend_id: number;
  status: 'pending' | 'accepted' | 'declined' | 'blocked';
  created_at: string;
  updated_at: string;
  user?: User;
  friend?: User;
}

export interface FriendRequest {
  id: number;
  from_user_id: number;
  to_user_id: number;
  status: 'pending' | 'accepted' | 'declined';
  message?: string;
  created_at: string;
  updated_at: string;
  from_user?: User;
  to_user?: User;
}

export interface CreateFriendRequestRequest {
  to_user_id: number;
  message?: string;
}

export interface RespondToFriendRequestRequest {
  request_id: number;
  action: 'accept' | 'decline';
}

export interface UserReport {
  id: number;
  reporter_id: number;
  reported_user_id: number;
  reason: 'spam' | 'harassment' | 'inappropriate_content' | 'fake_profile' | 'other';
  description?: string;
  status: 'pending' | 'reviewed' | 'resolved' | 'dismissed';
  created_at: string;
  updated_at: string;
  reporter?: User;
  reported_user?: User;
}

export interface CreateUserReportRequest {
  reported_user_id: number;
  reason: 'spam' | 'harassment' | 'inappropriate_content' | 'fake_profile' | 'other';
  description?: string;
}

// Fishing Companions
export interface FishingCompanion {
  id: number;
  user_id: number;
  companion_id: number;
  catch_id?: number;
  track_id?: number;
  status: 'pending' | 'accepted' | 'declined';
  created_at: string;
  updated_at: string;
  user?: User;
  companion?: User;
}

export interface AddFishingCompanionRequest {
  companion_id: number;
  catch_id?: number;
  track_id?: number;
}

export interface RespondToFishingCompanionRequest {
  companion_id: number;
  action: 'accept' | 'decline';
}
