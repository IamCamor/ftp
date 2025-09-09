// User types
export interface User {
  id: number;
  name: string;
  email: string;
  username: string;
  photo_url?: string;
  role: 'user' | 'pro' | 'premium';
  language: string;
  bonus_balance: number;
  followers_count: number;
  following_count: number;
  catches_count: number;
  points_count: number;
}

// Biometric data types
export interface BiometricData {
  id: number;
  user_id: number;
  fishing_session_id?: number;
  catch_record_id?: number;
  heart_rate?: number;
  hrv?: number;
  stress_level?: number;
  mood_index?: number;
  mood_emoji?: string;
  temperature?: number;
  steps?: number;
  calories_burned?: number;
  activity_level?: number;
  acceleration_x?: number;
  acceleration_y?: number;
  acceleration_z?: number;
  gyroscope_x?: number;
  gyroscope_y?: number;
  gyroscope_z?: number;
  watch_hand?: 'casting' | 'reeling';
  casts_count?: number;
  reels_count?: number;
  reels_meters?: number;
  recorded_at: string;
  created_at: string;
  updated_at: string;
}

// Fishing session types
export interface FishingSession {
  id: number;
  user_id: number;
  point_id?: number;
  name?: string;
  description?: string;
  started_at: string;
  ended_at?: string;
  status: 'active' | 'paused' | 'completed' | 'cancelled';
  watch_hand?: 'casting' | 'reeling';
  biometric_tracking: boolean;
  gps_tracking: boolean;
  mood_tracking: boolean;
  total_casts: number;
  total_reels: number;
  total_reels_meters: number;
  catches_count: number;
  total_weight: number;
  avg_heart_rate?: number;
  max_heart_rate?: number;
  min_heart_rate?: number;
  avg_hrv?: number;
  avg_stress_level?: number;
  avg_mood_index?: number;
  max_mood_index?: number;
  min_mood_index?: number;
  time_high_mood: number;
  time_medium_mood: number;
  time_low_mood: number;
  time_stressed: number;
  time_calm: number;
  start_latitude?: number;
  start_longitude?: number;
  end_latitude?: number;
  end_longitude?: number;
  total_distance: number;
  temperature?: number;
  humidity?: number;
  wind_speed?: number;
  weather_condition?: string;
  gps_track?: any[];
  mood_timeline?: any[];
  heart_rate_timeline?: any[];
  activity_timeline?: any[];
  session_summary?: string;
  coach_insights?: string;
  created_at: string;
  updated_at: string;
}

// Catch record types
export interface CatchRecord {
  id: number;
  user_id: number;
  point_id?: number;
  fishing_session_id?: number;
  fish_species: string;
  weight?: number;
  length?: number;
  latitude?: number;
  longitude?: number;
  notes?: string;
  caught_at: string;
  is_public: boolean;
  moderation_status: 'pending' | 'approved' | 'rejected' | 'pending_review';
  moderation_result?: any;
  moderated_at?: string;
  moderated_by?: number;
  created_at: string;
  updated_at: string;
}

// Point types
export interface Point {
  id: number;
  user_id: number;
  name: string;
  description?: string;
  latitude: number;
  longitude: number;
  type?: string;
  is_public: boolean;
  rating?: number;
  visits_count: number;
  is_blocked: boolean;
  blocked_at?: string;
  block_reason?: string;
  blocked_by?: number;
  created_at: string;
  updated_at: string;
}

// API response types
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  error?: string;
  errors?: Record<string, string[]>;
}

// Watch hand types
export type WatchHand = 'casting' | 'reeling';

// Session status types
export type SessionStatus = 'active' | 'paused' | 'completed' | 'cancelled';

// Mood types
export interface MoodData {
  index: number;
  emoji: string;
  description: string;
  color: string;
}

// Heart rate zone types
export interface HeartRateZone {
  zone: 'resting' | 'light' | 'moderate' | 'vigorous' | 'maximum';
  description: string;
  color: string;
  min: number;
  max: number;
}

// Biometric statistics types
export interface BiometricStats {
  total_sessions: number;
  avg_mood_index: number;
  avg_heart_rate: number;
  avg_stress_level: number;
  total_fishing_time: number;
  mood_trend: 'improving' | 'declining' | 'stable';
  best_session_mood: number;
  worst_session_mood: number;
}

// Session analytics types
export interface SessionAnalytics {
  session_id: number;
  duration: number;
  duration_human: string;
  mood_breakdown: {
    high: number;
    medium: number;
    low: number;
  };
  stress_breakdown: {
    stressed: number;
    calm: number;
  };
  mood_summary: string;
  peak_mood_moment?: {
    mood_index: number;
    timestamp: string;
    time_formatted: string;
  };
  coach_insights: string[];
  mood_timeline: any[];
  heart_rate_timeline: any[];
  activity_timeline: any[];
  biometric_stats: {
    avg_heart_rate?: number;
    max_heart_rate?: number;
    min_heart_rate?: number;
    avg_hrv?: number;
    avg_stress_level?: number;
    avg_mood_index?: number;
    max_mood_index?: number;
    min_mood_index?: number;
  };
  activity_stats: {
    total_casts: number;
    total_reels: number;
    total_reels_meters: number;
    casts_per_hour: number;
    reels_per_hour: number;
    meters_per_hour: number;
  };
  catch_stats: {
    catches_count: number;
    total_weight: number;
  };
}

// Navigation types
export type RootStackParamList = {
  Home: undefined;
  StartSession: undefined;
  ActiveSession: { sessionId: number };
  RecordCatch: { sessionId: number };
  SessionComplete: { sessionId: number };
  History: undefined;
  Settings: undefined;
  Coach: undefined;
  Analytics: { sessionId?: number };
  Profile: undefined;
};

// Watch sensor types
export interface WatchSensors {
  heartRate: number | null;
  hrv: number | null;
  temperature: number | null;
  steps: number | null;
  calories: number | null;
  acceleration: {
    x: number;
    y: number;
    z: number;
  } | null;
  gyroscope: {
    x: number;
    y: number;
    z: number;
  } | null;
}

// Watch settings types
export interface WatchSettings {
  biometricTracking: boolean;
  gpsTracking: boolean;
  moodTracking: boolean;
  watchHand: WatchHand;
  updateInterval: number; // seconds
  hapticFeedback: boolean;
  soundEnabled: boolean;
  autoSync: boolean;
  theme: 'light' | 'dark' | 'auto';
  language: string;
}

// Coach insight types
export interface CoachInsight {
  type: 'heart_rate' | 'mood' | 'stress' | 'activity' | 'catch';
  level: 'info' | 'warning' | 'success';
  message: string;
  timestamp: string;
  actionable: boolean;
}

// Chart data types
export interface ChartData {
  labels: string[];
  datasets: {
    data: number[];
    color?: (opacity: number) => string;
    strokeWidth?: number;
  }[];
}

// Theme types
export interface Theme {
  colors: {
    primary: string;
    secondary: string;
    background: string;
    surface: string;
    text: string;
    textSecondary: string;
    border: string;
    success: string;
    warning: string;
    error: string;
    info: string;
    mood: {
      high: string;
      medium: string;
      low: string;
    };
    heartRate: {
      resting: string;
      light: string;
      moderate: string;
      vigorous: string;
      maximum: string;
    };
  };
  spacing: {
    xs: number;
    sm: number;
    md: number;
    lg: number;
    xl: number;
  };
  borderRadius: {
    sm: number;
    md: number;
    lg: number;
  };
  fontSize: {
    xs: number;
    sm: number;
    md: number;
    lg: number;
    xl: number;
    xxl: number;
  };
}
