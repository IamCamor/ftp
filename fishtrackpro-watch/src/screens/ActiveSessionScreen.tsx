import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  Dimensions,
  Animated,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../types';
import { apiService } from '../services/api';
import { biometricService } from '../services/biometricService';
import BiometricCard from '../components/BiometricCard';
import { BiometricData } from '../types';
import { formatDuration, formatNumber } from '../utils/helpers';

type ActiveSessionScreenNavigationProp = StackNavigationProp<RootStackParamList, 'ActiveSession'>;
type ActiveSessionScreenRouteProp = RouteProp<RootStackParamList, 'ActiveSession'>;

const { width, height } = Dimensions.get('window');

const ActiveSessionScreen: React.FC = () => {
  const navigation = useNavigation<ActiveSessionScreenNavigationProp>();
  const route = useRoute<ActiveSessionScreenRouteProp>();
  const { sessionId } = route.params;

  const [sessionData, setSessionData] = useState<any>(null);
  const [currentBiometric, setCurrentBiometric] = useState<BiometricData | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isPaused, setIsPaused] = useState(false);
  const [sessionStartTime, setSessionStartTime] = useState<Date>(new Date());
  const [elapsedTime, setElapsedTime] = useState(0);
  const [castsCount, setCastsCount] = useState(0);
  const [reelsCount, setReelsCount] = useState(0);
  const [reelsMeters, setReelsMeters] = useState(0);

  const pulseAnimation = useRef(new Animated.Value(1)).current;
  const moodAnimation = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    loadSessionData();
    startTimer();
    startBiometricUpdates();
  }, []);

  useEffect(() => {
    if (currentBiometric) {
      animateMoodChange();
    }
  }, [currentBiometric]);

  const loadSessionData = async () => {
    try {
      const response = await apiService.getSessionStatus(sessionId);
      if (response.success && response.data) {
        setSessionData(response.data);
        setSessionStartTime(new Date(response.data.started_at));
        setCastsCount(response.data.total_casts);
        setReelsCount(response.data.total_reels);
        setReelsMeters(response.data.total_reels_meters);
        
        if (response.data.latest_biometric) {
          setCurrentBiometric(response.data.latest_biometric);
        }
      }
    } catch (error) {
      console.error('Error loading session data:', error);
    }
  };

  const startTimer = () => {
    const timer = setInterval(() => {
      if (!isPaused) {
        setElapsedTime(prev => prev + 1);
      }
    }, 1000);

    return () => clearInterval(timer);
  };

  const startBiometricUpdates = () => {
    const interval = setInterval(async () => {
      if (!isPaused && biometricService.isCurrentlyMonitoring()) {
        try {
          const sensors = await biometricService.collectBiometricData();
          const biometricData: BiometricData = {
            id: 0,
            user_id: 0,
            fishing_session_id: sessionId,
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
          console.error('Error updating biometric data:', error);
        }
      }
    }, 30000); // Update every 30 seconds

    return () => clearInterval(interval);
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

  const animateMoodChange = () => {
    Animated.sequence([
      Animated.timing(moodAnimation, {
        toValue: 1.2,
        duration: 200,
        useNativeDriver: true,
      }),
      Animated.timing(moodAnimation, {
        toValue: 1,
        duration: 200,
        useNativeDriver: true,
      }),
    ]).start();
  };

  const animatePulse = () => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnimation, {
          toValue: 1.1,
          duration: 1000,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnimation, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
        }),
      ])
    ).start();
  };

  const handleRecordCatch = () => {
    navigation.navigate('RecordCatch', { sessionId });
  };

  const handlePauseResume = async () => {
    if (isLoading) return;

    try {
      setIsLoading(true);
      
      if (isPaused) {
        await apiService.resumeSession(sessionId);
        setIsPaused(false);
      } else {
        await apiService.pauseSession(sessionId);
        setIsPaused(true);
      }
    } catch (error) {
      console.error('Error pausing/resuming session:', error);
      Alert.alert('Ошибка', 'Не удалось изменить статус сессии');
    } finally {
      setIsLoading(false);
    }
  };

  const handleEndSession = async () => {
    Alert.alert(
      'Завершить сессию',
      'Вы уверены, что хотите завершить текущую сессию рыбалки?',
      [
        { text: 'Отмена', style: 'cancel' },
        { 
          text: 'Завершить', 
          style: 'destructive',
          onPress: async () => {
            try {
              setIsLoading(true);
              await biometricService.stopMonitoring();
              
              const response = await apiService.endSession(sessionId);
              
              if (response.success) {
                navigation.navigate('SessionComplete', { sessionId });
              } else {
                Alert.alert('Ошибка', response.message || 'Не удалось завершить сессию');
              }
            } catch (error) {
              console.error('Error ending session:', error);
              Alert.alert('Ошибка', 'Не удалось завершить сессию');
            } finally {
              setIsLoading(false);
            }
          }
        },
      ]
    );
  };

  const incrementCasts = () => {
    setCastsCount(prev => prev + 1);
  };

  const incrementReels = () => {
    setReelsCount(prev => prev + 1);
    setReelsMeters(prev => prev + 0.5); // Assuming 0.5 meters per reel
  };

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={['#4CAF50', '#45a049']}
        style={styles.header}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <Text style={styles.timer}>{formatDuration(elapsedTime)}</Text>
        <Text style={styles.status}>
          {isPaused ? 'Приостановлено' : 'Активная сессия'}
        </Text>
      </LinearGradient>

      <View style={styles.content}>
        <View style={styles.metricsRow}>
          <TouchableOpacity
            style={styles.metricCard}
            onPress={incrementCasts}
            activeOpacity={0.7}
          >
            <Text style={styles.metricValue}>{castsCount}</Text>
            <Text style={styles.metricLabel}>Забросы</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.metricCard}
            onPress={incrementReels}
            activeOpacity={0.7}
          >
            <Text style={styles.metricValue}>{formatNumber(reelsMeters, 0)}</Text>
            <Text style={styles.metricLabel}>Метры</Text>
          </TouchableOpacity>
        </View>

        <TouchableOpacity
          style={styles.catchButton}
          onPress={handleRecordCatch}
          activeOpacity={0.8}
        >
          <LinearGradient
            colors={['#FF5722', '#E64A19']}
            style={styles.catchButtonGradient}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            <Text style={styles.catchButtonEmoji}>🎣</Text>
            <Text style={styles.catchButtonText}>Поймал!</Text>
          </LinearGradient>
        </TouchableOpacity>

        {currentBiometric && (
          <View style={styles.biometricSection}>
            <Animated.View style={{ transform: [{ scale: moodAnimation }] }}>
              <BiometricCard data={currentBiometric} compact />
            </Animated.View>
          </View>
        )}

        <View style={styles.actions}>
          <TouchableOpacity
            style={[styles.actionButton, styles.pauseButton]}
            onPress={handlePauseResume}
            activeOpacity={0.7}
            disabled={isLoading}
          >
            <Text style={styles.actionButtonText}>
              {isPaused ? '▶️ Продолжить' : '⏸️ Пауза'}
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.actionButton, styles.endButton]}
            onPress={handleEndSession}
            activeOpacity={0.7}
            disabled={isLoading}
          >
            <Text style={styles.actionButtonText}>🏁 Завершить</Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    padding: 24,
    alignItems: 'center',
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
  },
  timer: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  status: {
    fontSize: 16,
    color: '#fff',
    opacity: 0.9,
  },
  content: {
    flex: 1,
    padding: 16,
  },
  metricsRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 24,
  },
  metricCard: {
    backgroundColor: '#fff',
    padding: 20,
    borderRadius: 16,
    alignItems: 'center',
    flex: 1,
    marginHorizontal: 8,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
  },
  metricValue: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
  },
  metricLabel: {
    fontSize: 14,
    color: '#666',
  },
  catchButton: {
    marginBottom: 24,
    borderRadius: 20,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4.65,
    elevation: 8,
  },
  catchButtonGradient: {
    padding: 24,
    borderRadius: 20,
    alignItems: 'center',
  },
  catchButtonEmoji: {
    fontSize: 48,
    marginBottom: 8,
  },
  catchButtonText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  biometricSection: {
    marginBottom: 24,
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    flex: 1,
    padding: 16,
    marginHorizontal: 4,
    borderRadius: 12,
    alignItems: 'center',
  },
  pauseButton: {
    backgroundColor: '#FF9800',
  },
  endButton: {
    backgroundColor: '#F44336',
  },
  actionButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
  },
});

export default ActiveSessionScreen;
