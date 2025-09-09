import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Alert,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../types';
import { apiService } from '../services/api';
import { biometricService } from '../services/biometricService';
import BiometricCard from '../components/BiometricCard';
import StatsCard from '../components/StatsCard';
import { BiometricStats, BiometricData } from '../types';

type HomeScreenNavigationProp = StackNavigationProp<RootStackParamList, 'Home'>;

const { width, height } = Dimensions.get('window');

const HomeScreen: React.FC = () => {
  const navigation = useNavigation<HomeScreenNavigationProp>();
  const [currentBiometric, setCurrentBiometric] = useState<BiometricData | null>(null);
  const [stats, setStats] = useState<BiometricStats | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [permissionsGranted, setPermissionsGranted] = useState(false);

  useEffect(() => {
    initializeApp();
  }, []);

  const initializeApp = async () => {
    try {
      setIsLoading(true);
      
      // Request permissions
      const permissions = await biometricService.requestPermissions();
      setPermissionsGranted(permissions);
      
      if (permissions) {
        // Load current biometric data
        await loadCurrentBiometricData();
      }
      
      // Load user stats
      await loadUserStats();
    } catch (error) {
      console.error('Error initializing app:', error);
      Alert.alert('Ошибка', 'Не удалось инициализировать приложение');
    } finally {
      setIsLoading(false);
    }
  };

  const loadCurrentBiometricData = async () => {
    try {
      const sensors = await biometricService.collectBiometricData();
      const biometricData: BiometricData = {
        id: 0,
        user_id: 0,
        heart_rate: sensors.heartRate,
        hrv: sensors.hrv,
        stress_level: sensors.heartRate ? calculateStressLevel(sensors.heartRate, sensors.hrv) : null,
        mood_index: calculateMoodIndex(sensors),
        mood_emoji: getMoodEmoji(calculateMoodIndex(sensors)),
        temperature: sensors.temperature,
        steps: sensors.steps,
        calories_burned: sensors.calories,
        activity_level: biometricService.calculateActivityLevel(sensors),
        acceleration_x: sensors.acceleration?.x,
        acceleration_y: sensors.acceleration?.y,
        acceleration_z: sensors.acceleration?.z,
        gyroscope_x: sensors.gyroscope?.x,
        gyroscope_y: sensors.gyroscope?.y,
        gyroscope_z: sensors.gyroscope?.z,
        recorded_at: new Date().toISOString(),
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      };
      
      setCurrentBiometric(biometricData);
    } catch (error) {
      console.error('Error loading biometric data:', error);
    }
  };

  const loadUserStats = async () => {
    try {
      const response = await apiService.getUserBiometricStats(30);
      if (response.success && response.data) {
        setStats(response.data);
      }
    } catch (error) {
      console.error('Error loading user stats:', error);
    }
  };

  const calculateStressLevel = (heartRate: number, hrv?: number | null): number => {
    let stressScore = 50;
    
    if (heartRate > 120) {
      stressScore += 30;
    } else if (heartRate > 100) {
      stressScore += 15;
    } else if (heartRate < 60) {
      stressScore -= 20;
    }
    
    if (hrv !== null && hrv !== undefined) {
      if (hrv < 20) {
        stressScore += 25;
      } else if (hrv < 30) {
        stressScore += 15;
      } else if (hrv > 50) {
        stressScore -= 20;
      }
    }
    
    return Math.max(0, Math.min(100, stressScore));
  };

  const calculateMoodIndex = (sensors: any): number => {
    let moodScore = 50;
    
    if (sensors.heartRate) {
      if (sensors.heartRate >= 60 && sensors.heartRate <= 100) {
        moodScore += 15;
      } else if (sensors.heartRate > 140) {
        moodScore -= 20;
      } else if (sensors.heartRate > 100) {
        moodScore -= 10;
      }
    }
    
    if (sensors.hrv) {
      if (sensors.hrv > 50) {
        moodScore += 20;
      } else if (sensors.hrv < 20) {
        moodScore -= 15;
      }
    }
    
    return Math.max(0, Math.min(100, moodScore));
  };

  const getMoodEmoji = (moodIndex: number): string => {
    if (moodIndex >= 80) return '😊';
    if (moodIndex >= 60) return '🙂';
    if (moodIndex >= 40) return '😐';
    if (moodIndex >= 20) return '😕';
    return '😩';
  };

  const handleStartFishing = () => {
    navigation.navigate('StartSession');
  };

  const handleViewHistory = () => {
    navigation.navigate('History');
  };

  const handleOpenSettings = () => {
    navigation.navigate('Settings');
  };

  const handleOpenCoach = () => {
    navigation.navigate('Coach');
  };

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <Text style={styles.loadingText}>Загрузка...</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <LinearGradient
        colors={['#4CAF50', '#45a049']}
        style={styles.header}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <Text style={styles.appTitle}>FishTrackPro</Text>
        <Text style={styles.appSubtitle}>Умные часы для рыбалки</Text>
      </LinearGradient>

      {!permissionsGranted && (
        <View style={styles.permissionWarning}>
          <Text style={styles.warningText}>
            ⚠️ Для полной функциональности необходимо разрешить доступ к датчикам
          </Text>
        </View>
      )}

      {currentBiometric && (
        <View style={styles.currentBiometric}>
          <Text style={styles.sectionTitle}>Текущее состояние</Text>
          <BiometricCard data={currentBiometric} compact />
        </View>
      )}

      <View style={styles.mainActions}>
        <TouchableOpacity
          style={styles.primaryButton}
          onPress={handleStartFishing}
          activeOpacity={0.8}
        >
          <LinearGradient
            colors={['#2196F3', '#1976D2']}
            style={styles.buttonGradient}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            <Text style={styles.buttonEmoji}>🎣</Text>
            <Text style={styles.primaryButtonText}>Начать рыбалку</Text>
          </LinearGradient>
        </TouchableOpacity>

        <View style={styles.secondaryActions}>
          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={handleViewHistory}
            activeOpacity={0.7}
          >
            <Text style={styles.secondaryButtonEmoji}>📊</Text>
            <Text style={styles.secondaryButtonText}>История</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={handleOpenCoach}
            activeOpacity={0.7}
          >
            <Text style={styles.secondaryButtonEmoji}>🤖</Text>
            <Text style={styles.secondaryButtonText}>Тренер</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={handleOpenSettings}
            activeOpacity={0.7}
          >
            <Text style={styles.secondaryButtonEmoji}>⚙️</Text>
            <Text style={styles.secondaryButtonText}>Настройки</Text>
          </TouchableOpacity>
        </View>
      </View>

      {stats && (
        <View style={styles.statsSection}>
          <Text style={styles.sectionTitle}>Статистика за 30 дней</Text>
          <StatsCard stats={stats} showDetails={false} />
        </View>
      )}

      <View style={styles.footer}>
        <Text style={styles.footerText}>
          Версия 1.0.0 • {new Date().toLocaleDateString('ru-RU')}
        </Text>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f5f5f5',
  },
  loadingText: {
    fontSize: 18,
    color: '#666',
  },
  header: {
    padding: 24,
    alignItems: 'center',
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
  },
  appTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  appSubtitle: {
    fontSize: 16,
    color: '#fff',
    opacity: 0.9,
  },
  permissionWarning: {
    backgroundColor: '#FFF3CD',
    margin: 16,
    padding: 12,
    borderRadius: 8,
    borderLeftWidth: 4,
    borderLeftColor: '#FFC107',
  },
  warningText: {
    fontSize: 14,
    color: '#856404',
  },
  currentBiometric: {
    margin: 16,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  mainActions: {
    margin: 16,
  },
  primaryButton: {
    marginBottom: 20,
    borderRadius: 16,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4.65,
    elevation: 8,
  },
  buttonGradient: {
    padding: 20,
    borderRadius: 16,
    alignItems: 'center',
  },
  buttonEmoji: {
    fontSize: 32,
    marginBottom: 8,
  },
  primaryButtonText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  secondaryActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  secondaryButton: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 16,
    marginHorizontal: 4,
    borderRadius: 12,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
  },
  secondaryButtonEmoji: {
    fontSize: 24,
    marginBottom: 8,
  },
  secondaryButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
  },
  statsSection: {
    margin: 16,
  },
  footer: {
    alignItems: 'center',
    padding: 20,
  },
  footerText: {
    fontSize: 12,
    color: '#666',
  },
});

export default HomeScreen;
