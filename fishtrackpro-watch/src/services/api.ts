import axios, { AxiosInstance, AxiosResponse } from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { 
  ApiResponse, 
  FishingSession, 
  BiometricData, 
  CatchRecord, 
  BiometricStats, 
  SessionAnalytics,
  WatchSensors,
  WatchSettings
} from '../types';

class ApiService {
  private api: AxiosInstance;
  private baseURL: string;

  constructor() {
    this.baseURL = __DEV__ 
      ? 'http://localhost:8000/api/v1' 
      : 'https://api.fishtrackpro.com/api/v1';
    
    this.api = axios.create({
      baseURL: this.baseURL,
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    this.setupInterceptors();
  }

  private setupInterceptors(): void {
    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      async (config) => {
        const token = await AsyncStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor to handle errors
    this.api.interceptors.response.use(
      (response: AxiosResponse) => {
        return response;
      },
      async (error) => {
        if (error.response?.status === 401) {
          // Token expired, clear storage and redirect to login
          await AsyncStorage.multiRemove(['auth_token', 'user_data']);
          // You might want to emit an event here to handle logout
        }
        return Promise.reject(error);
      }
    );
  }

  // Authentication methods
  async login(email: string, password: string): Promise<ApiResponse> {
    const response = await this.api.post('/auth/login', { email, password });
    return response.data;
  }

  async register(userData: any): Promise<ApiResponse> {
    const response = await this.api.post('/auth/register', userData);
    return response.data;
  }

  async logout(): Promise<void> {
    await AsyncStorage.multiRemove(['auth_token', 'user_data']);
  }

  // Watch API methods
  async startSession(sessionData: {
    point_id?: number;
    watch_hand: 'casting' | 'reeling';
    biometric_tracking?: boolean;
    gps_tracking?: boolean;
    mood_tracking?: boolean;
    name?: string;
    description?: string;
    start_latitude?: number;
    start_longitude?: number;
  }): Promise<ApiResponse<{ session_id: number; started_at: string; watch_hand: string; watch_hand_description: string }>> {
    const response = await this.api.post('/watch/start-session', sessionData);
    return response.data;
  }

  async recordBiometricData(biometricData: {
    session_id: number;
    heart_rate?: number;
    hrv?: number;
    stress_level?: number;
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
    casts_count?: number;
    reels_count?: number;
    reels_meters?: number;
    recorded_at?: string;
  }): Promise<ApiResponse<{
    biometric_id: number;
    mood_index: number;
    mood_emoji: string;
    mood_description: string;
    stress_level: number;
    stress_description: string;
    heart_rate: number;
    heart_rate_zone: string;
    heart_rate_zone_description: string;
    recorded_at: string;
  }>> {
    const response = await this.api.post('/watch/record-biometric', biometricData);
    return response.data;
  }

  async recordCatch(catchData: {
    session_id: number;
    fish_species: string;
    weight?: number;
    length?: number;
    latitude?: number;
    longitude?: number;
    notes?: string;
    heart_rate?: number;
    hrv?: number;
    stress_level?: number;
  }): Promise<ApiResponse<{
    catch_id: number;
    fish_species: string;
    weight?: number;
    length?: number;
    caught_at: string;
    biometric_data: {
      mood_index: number;
      mood_emoji: string;
      mood_description: string;
      heart_rate: number;
      stress_level: number;
    };
    session_stats: {
      catches_count: number;
      total_weight: number;
    };
  }>> {
    const response = await this.api.post('/watch/record-catch', catchData);
    return response.data;
  }

  async getSessionStatus(sessionId: number): Promise<ApiResponse<{
    session_id: number;
    status: string;
    status_description: string;
    duration: number;
    duration_human: string;
    watch_hand: string;
    watch_hand_description: string;
    total_casts: number;
    total_reels: number;
    total_reels_meters: number;
    catches_count: number;
    total_weight: number;
    casts_per_hour: number;
    reels_per_hour: number;
    meters_per_hour: number;
    latest_biometric?: {
      mood_index: number;
      mood_emoji: string;
      mood_description: string;
      heart_rate: number;
      heart_rate_zone: string;
      heart_rate_zone_description: string;
      stress_level: number;
      stress_description: string;
      recorded_at: string;
    };
  }>> {
    const response = await this.api.get(`/watch/session-status?session_id=${sessionId}`);
    return response.data;
  }

  async getUserBiometricStats(days: number = 30): Promise<ApiResponse<BiometricStats>> {
    const response = await this.api.get(`/watch/biometric-stats?days=${days}`);
    return response.data;
  }

  async getSessionAnalytics(sessionId: number): Promise<ApiResponse<SessionAnalytics>> {
    const response = await this.api.get(`/watch/session-analytics?session_id=${sessionId}`);
    return response.data;
  }

  // Utility methods
  async pauseSession(sessionId: number): Promise<ApiResponse> {
    const response = await this.api.post('/watch/pause-session', { session_id: sessionId });
    return response.data;
  }

  async resumeSession(sessionId: number): Promise<ApiResponse> {
    const response = await this.api.post('/watch/resume-session', { session_id: sessionId });
    return response.data;
  }

  async endSession(sessionId: number, endData?: {
    end_latitude?: number;
    end_longitude?: number;
    total_distance?: number;
    gps_track?: any[];
  }): Promise<ApiResponse<{
    session_id: number;
    status: string;
    duration: number;
    duration_human: string;
    total_casts: number;
    total_reels: number;
    total_reels_meters: number;
    catches_count: number;
    total_weight: number;
    avg_mood_index: number;
    mood_breakdown: any;
    mood_summary: string;
    coach_insights: string[];
    peak_mood_moment?: any;
  }>> {
    const response = await this.api.post('/watch/end-session', {
      session_id: sessionId,
      ...endData
    });
    return response.data;
  }

  // Offline support methods
  async storeOfflineData(key: string, data: any): Promise<void> {
    try {
      const existingData = await AsyncStorage.getItem('offline_data');
      const offlineData = existingData ? JSON.parse(existingData) : {};
      offlineData[key] = data;
      await AsyncStorage.setItem('offline_data', JSON.stringify(offlineData));
    } catch (error) {
      console.error('Error storing offline data:', error);
    }
  }

  async getOfflineData(key: string): Promise<any> {
    try {
      const offlineData = await AsyncStorage.getItem('offline_data');
      if (offlineData) {
        const parsed = JSON.parse(offlineData);
        return parsed[key];
      }
      return null;
    } catch (error) {
      console.error('Error getting offline data:', error);
      return null;
    }
  }

  async syncOfflineData(): Promise<void> {
    try {
      const offlineData = await AsyncStorage.getItem('offline_data');
      if (offlineData) {
        const parsed = JSON.parse(offlineData);
        
        // Sync biometric data
        if (parsed.biometric_data) {
          for (const data of parsed.biometric_data) {
            try {
              await this.recordBiometricData(data);
            } catch (error) {
              console.error('Error syncing biometric data:', error);
            }
          }
        }

        // Sync catches
        if (parsed.catches) {
          for (const catchData of parsed.catches) {
            try {
              await this.recordCatch(catchData);
            } catch (error) {
              console.error('Error syncing catch data:', error);
            }
          }
        }

        // Clear offline data after successful sync
        await AsyncStorage.removeItem('offline_data');
      }
    } catch (error) {
      console.error('Error syncing offline data:', error);
    }
  }

  // Health data integration
  async requestHealthPermissions(): Promise<boolean> {
    // This would integrate with react-native-health
    // Implementation depends on platform (iOS HealthKit, Android Health Connect)
    return true;
  }

  async getHealthData(): Promise<WatchSensors> {
    // This would fetch data from health platforms
    // Implementation depends on platform
    return {
      heartRate: null,
      hrv: null,
      temperature: null,
      steps: null,
      calories: null,
      acceleration: null,
      gyroscope: null,
    };
  }

  // Settings management
  async saveSettings(settings: WatchSettings): Promise<void> {
    await AsyncStorage.setItem('watch_settings', JSON.stringify(settings));
  }

  async getSettings(): Promise<WatchSettings> {
    const settings = await AsyncStorage.getItem('watch_settings');
    if (settings) {
      return JSON.parse(settings);
    }
    
    // Return default settings
    return {
      biometricTracking: true,
      gpsTracking: true,
      moodTracking: true,
      watchHand: 'casting',
      updateInterval: 30,
      hapticFeedback: true,
      soundEnabled: true,
      autoSync: true,
      theme: 'auto',
      language: 'en',
    };
  }
}

export const apiService = new ApiService();
export default apiService;
