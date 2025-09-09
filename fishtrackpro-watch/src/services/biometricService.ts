import { Platform, PermissionsAndroid } from 'react-native';
import { 
  WatchSensors, 
  BiometricData, 
  HeartRateZone, 
  MoodData,
  WatchSettings 
} from '../types';
import { apiService } from './api';

class BiometricService {
  private isMonitoring: boolean = false;
  private monitoringInterval: NodeJS.Timeout | null = null;
  private currentSessionId: number | null = null;
  private settings: WatchSettings | null = null;

  constructor() {
    this.loadSettings();
  }

  private async loadSettings(): Promise<void> {
    this.settings = await apiService.getSettings();
  }

  // Permission management
  async requestPermissions(): Promise<boolean> {
    try {
      if (Platform.OS === 'android') {
        const permissions = [
          PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
          PermissionsAndroid.PERMISSIONS.ACCESS_COARSE_LOCATION,
          PermissionsAndroid.PERMISSIONS.BODY_SENSORS,
          PermissionsAndroid.PERMISSIONS.ACTIVITY_RECOGNITION,
        ];

        const granted = await PermissionsAndroid.requestMultiple(permissions);
        
        return Object.values(granted).every(
          permission => permission === PermissionsAndroid.RESULTS.GRANTED
        );
      } else {
        // iOS permissions are handled through Info.plist
        return true;
      }
    } catch (error) {
      console.error('Error requesting permissions:', error);
      return false;
    }
  }

  // Start biometric monitoring
  async startMonitoring(sessionId: number): Promise<void> {
    if (this.isMonitoring) {
      return;
    }

    this.currentSessionId = sessionId;
    this.isMonitoring = true;

    // Start monitoring at specified interval
    const interval = this.settings?.updateInterval || 30;
    this.monitoringInterval = setInterval(async () => {
      await this.collectAndSendBiometricData();
    }, interval * 1000);

    // Initial data collection
    await this.collectAndSendBiometricData();
  }

  // Stop biometric monitoring
  stopMonitoring(): void {
    if (this.monitoringInterval) {
      clearInterval(this.monitoringInterval);
      this.monitoringInterval = null;
    }
    this.isMonitoring = false;
    this.currentSessionId = null;
  }

  // Collect biometric data from sensors
  private async collectBiometricData(): Promise<WatchSensors> {
    try {
      // This would integrate with actual sensor APIs
      // For now, we'll simulate data collection
      
      const sensors: WatchSensors = {
        heartRate: await this.getHeartRate(),
        hrv: await this.getHRV(),
        temperature: await this.getTemperature(),
        steps: await this.getSteps(),
        calories: await this.getCalories(),
        acceleration: await this.getAcceleration(),
        gyroscope: await this.getGyroscope(),
      };

      return sensors;
    } catch (error) {
      console.error('Error collecting biometric data:', error);
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
  }

  // Collect and send biometric data
  private async collectAndSendBiometricData(): Promise<void> {
    if (!this.currentSessionId || !this.settings?.biometricTracking) {
      return;
    }

    try {
      const sensors = await this.collectBiometricData();
      
      const biometricData = {
        session_id: this.currentSessionId,
        heart_rate: sensors.heartRate,
        hrv: sensors.hrv,
        temperature: sensors.temperature,
        steps: sensors.steps,
        calories_burned: sensors.calories,
        activity_level: this.calculateActivityLevel(sensors),
        acceleration_x: sensors.acceleration?.x,
        acceleration_y: sensors.acceleration?.y,
        acceleration_z: sensors.acceleration?.z,
        gyroscope_x: sensors.gyroscope?.x,
        gyroscope_y: sensors.gyroscope?.y,
        gyroscope_z: sensors.gyroscope?.z,
        recorded_at: new Date().toISOString(),
      };

      // Send to API
      const response = await apiService.recordBiometricData(biometricData);
      
      if (response.success) {
        // Emit event for UI updates
        this.emitBiometricUpdate(response.data);
      }
    } catch (error) {
      console.error('Error sending biometric data:', error);
      // Store offline for later sync
      await this.storeOfflineBiometricData();
    }
  }

  // Simulate sensor data collection (replace with actual sensor APIs)
  private async getHeartRate(): Promise<number | null> {
    // Simulate heart rate between 60-120 bpm
    return Math.floor(Math.random() * 60) + 60;
  }

  private async getHRV(): Promise<number | null> {
    // Simulate HRV between 20-80 ms
    return Math.floor(Math.random() * 60) + 20;
  }

  private async getTemperature(): Promise<number | null> {
    // Simulate body temperature between 36.0-37.5°C
    return Math.round((Math.random() * 1.5 + 36.0) * 10) / 10;
  }

  private async getSteps(): Promise<number | null> {
    // Simulate steps (incrementing)
    return Math.floor(Math.random() * 10) + 1;
  }

  private async getCalories(): Promise<number | null> {
    // Simulate calories burned
    return Math.round((Math.random() * 5 + 1) * 10) / 10;
  }

  private async getAcceleration(): Promise<{ x: number; y: number; z: number } | null> {
    // Simulate acceleration data
    return {
      x: Math.round((Math.random() * 2 - 1) * 100) / 100,
      y: Math.round((Math.random() * 2 - 1) * 100) / 100,
      z: Math.round((Math.random() * 2 - 1) * 100) / 100,
    };
  }

  private async getGyroscope(): Promise<{ x: number; y: number; z: number } | null> {
    // Simulate gyroscope data
    return {
      x: Math.round((Math.random() * 0.2 - 0.1) * 100) / 100,
      y: Math.round((Math.random() * 0.2 - 0.1) * 100) / 100,
      z: Math.round((Math.random() * 0.2 - 0.1) * 100) / 100,
    };
  }

  // Calculate activity level based on sensor data
  private calculateActivityLevel(sensors: WatchSensors): number {
    let activityLevel = 0;

    // Heart rate contribution
    if (sensors.heartRate) {
      if (sensors.heartRate > 100) {
        activityLevel += 40;
      } else if (sensors.heartRate > 80) {
        activityLevel += 20;
      } else {
        activityLevel += 10;
      }
    }

    // Acceleration contribution
    if (sensors.acceleration) {
      const magnitude = Math.sqrt(
        sensors.acceleration.x ** 2 +
        sensors.acceleration.y ** 2 +
        sensors.acceleration.z ** 2
      );
      activityLevel += Math.min(magnitude * 20, 30);
    }

    // Gyroscope contribution
    if (sensors.gyroscope) {
      const magnitude = Math.sqrt(
        sensors.gyroscope.x ** 2 +
        sensors.gyroscope.y ** 2 +
        sensors.gyroscope.z ** 2
      );
      activityLevel += Math.min(magnitude * 30, 20);
    }

    return Math.min(activityLevel, 100);
  }

  // Get heart rate zone
  getHeartRateZone(heartRate: number): HeartRateZone {
    if (heartRate < 60) {
      return {
        zone: 'resting',
        description: 'Покой',
        color: '#4CAF50',
        min: 0,
        max: 59,
      };
    } else if (heartRate < 100) {
      return {
        zone: 'light',
        description: 'Легкая активность',
        color: '#8BC34A',
        min: 60,
        max: 99,
      };
    } else if (heartRate < 140) {
      return {
        zone: 'moderate',
        description: 'Умеренная активность',
        color: '#FF9800',
        min: 100,
        max: 139,
      };
    } else if (heartRate < 180) {
      return {
        zone: 'vigorous',
        description: 'Интенсивная активность',
        color: '#FF5722',
        min: 140,
        max: 179,
      };
    } else {
      return {
        zone: 'maximum',
        description: 'Максимальная активность',
        color: '#F44336',
        min: 180,
        max: 220,
      };
    }
  }

  // Get mood data
  getMoodData(moodIndex: number): MoodData {
    if (moodIndex >= 80) {
      return {
        index: moodIndex,
        emoji: '😊',
        description: 'Отличное настроение',
        color: '#4CAF50',
      };
    } else if (moodIndex >= 60) {
      return {
        index: moodIndex,
        emoji: '🙂',
        description: 'Хорошее настроение',
        color: '#8BC34A',
      };
    } else if (moodIndex >= 40) {
      return {
        index: moodIndex,
        emoji: '😐',
        description: 'Нейтральное настроение',
        color: '#FF9800',
      };
    } else if (moodIndex >= 20) {
      return {
        index: moodIndex,
        emoji: '😕',
        description: 'Плохое настроение',
        color: '#FF5722',
      };
    } else {
      return {
        index: moodIndex,
        emoji: '😩',
        description: 'Очень плохое настроение',
        color: '#F44336',
      };
    }
  }

  // Store offline biometric data
  private async storeOfflineBiometricData(): Promise<void> {
    try {
      const sensors = await this.collectBiometricData();
      const biometricData = {
        session_id: this.currentSessionId,
        heart_rate: sensors.heartRate,
        hrv: sensors.hrv,
        temperature: sensors.temperature,
        steps: sensors.steps,
        calories_burned: sensors.calories,
        activity_level: this.calculateActivityLevel(sensors),
        acceleration_x: sensors.acceleration?.x,
        acceleration_y: sensors.acceleration?.y,
        acceleration_z: sensors.acceleration?.z,
        gyroscope_x: sensors.gyroscope?.x,
        gyroscope_y: sensors.gyroscope?.y,
        gyroscope_z: sensors.gyroscope?.z,
        recorded_at: new Date().toISOString(),
      };

      await apiService.storeOfflineData('biometric_data', biometricData);
    } catch (error) {
      console.error('Error storing offline biometric data:', error);
    }
  }

  // Emit biometric update event
  private emitBiometricUpdate(data: any): void {
    // This would emit an event that components can listen to
    // Implementation depends on your state management solution
    console.log('Biometric update:', data);
  }

  // Get current monitoring status
  isCurrentlyMonitoring(): boolean {
    return this.isMonitoring;
  }

  // Get current session ID
  getCurrentSessionId(): number | null {
    return this.currentSessionId;
  }

  // Update settings
  async updateSettings(newSettings: Partial<WatchSettings>): Promise<void> {
    if (this.settings) {
      this.settings = { ...this.settings, ...newSettings };
      await apiService.saveSettings(this.settings);
    }
  }

  // Get current settings
  getSettings(): WatchSettings | null {
    return this.settings;
  }
}

export const biometricService = new BiometricService();
export default biometricService;
