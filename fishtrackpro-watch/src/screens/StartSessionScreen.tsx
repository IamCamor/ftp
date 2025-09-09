import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  Alert,
  Switch,
  TextInput,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList, WatchHand } from '../types';
import { apiService } from '../services/api';
import { biometricService } from '../services/biometricService';

type StartSessionScreenNavigationProp = StackNavigationProp<RootStackParamList, 'StartSession'>;

const StartSessionScreen: React.FC = () => {
  const navigation = useNavigation<StartSessionScreenNavigationProp>();
  const [watchHand, setWatchHand] = useState<WatchHand>('casting');
  const [biometricTracking, setBiometricTracking] = useState(true);
  const [gpsTracking, setGpsTracking] = useState(true);
  const [moodTracking, setMoodTracking] = useState(true);
  const [sessionName, setSessionName] = useState('');
  const [sessionDescription, setSessionDescription] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [currentLocation, setCurrentLocation] = useState<{ latitude: number; longitude: number } | null>(null);

  useEffect(() => {
    getCurrentLocation();
  }, []);

  const getCurrentLocation = async () => {
    try {
      // This would integrate with actual GPS service
      // For now, we'll simulate location
      setCurrentLocation({
        latitude: 55.7558,
        longitude: 37.6176,
      });
    } catch (error) {
      console.error('Error getting location:', error);
    }
  };

  const handleStartSession = async () => {
    if (isLoading) return;

    try {
      setIsLoading(true);

      const sessionData = {
        watch_hand: watchHand,
        biometric_tracking: biometricTracking,
        gps_tracking: gpsTracking,
        mood_tracking: moodTracking,
        name: sessionName || undefined,
        description: sessionDescription || undefined,
        start_latitude: currentLocation?.latitude,
        start_longitude: currentLocation?.longitude,
      };

      const response = await apiService.startSession(sessionData);

      if (response.success && response.data) {
        // Start biometric monitoring
        if (biometricTracking) {
          await biometricService.startMonitoring(response.data.session_id);
        }

        // Navigate to active session screen
        navigation.navigate('ActiveSession', {
          sessionId: response.data.session_id,
        });
      } else {
        Alert.alert('Ошибка', response.message || 'Не удалось начать сессию');
      }
    } catch (error) {
      console.error('Error starting session:', error);
      Alert.alert('Ошибка', 'Не удалось начать сессию');
    } finally {
      setIsLoading(false);
    }
  };

  const handleCancel = () => {
    navigation.goBack();
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <LinearGradient
        colors={['#2196F3', '#1976D2']}
        style={styles.header}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <Text style={styles.title}>Начать рыбалку</Text>
        <Text style={styles.subtitle}>Настройте параметры сессии</Text>
      </LinearGradient>

      <View style={styles.content}>
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>На какой руке часы?</Text>
          <View style={styles.handSelection}>
            <TouchableOpacity
              style={[
                styles.handOption,
                watchHand === 'casting' && styles.handOptionSelected,
              ]}
              onPress={() => setWatchHand('casting')}
              activeOpacity={0.7}
            >
              <Text style={styles.handEmoji}>🎯</Text>
              <Text style={[
                styles.handText,
                watchHand === 'casting' && styles.handTextSelected,
              ]}>
                На руке заброса
              </Text>
              <Text style={[
                styles.handDescription,
                watchHand === 'casting' && styles.handDescriptionSelected,
              ]}>
                Акцент на подсчет забросов
              </Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={[
                styles.handOption,
                watchHand === 'reeling' && styles.handOptionSelected,
              ]}
              onPress={() => setWatchHand('reeling')}
              activeOpacity={0.7}
            >
              <Text style={styles.handEmoji}>🎣</Text>
              <Text style={[
                styles.handText,
                watchHand === 'reeling' && styles.handTextSelected,
              ]}>
                На руке сматывания
              </Text>
              <Text style={[
                styles.handDescription,
                watchHand === 'reeling' && styles.handDescriptionSelected,
              ]}>
                Акцент на подсчет метров
              </Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Отслеживание</Text>
          
          <View style={styles.trackingOption}>
            <View style={styles.trackingInfo}>
              <Text style={styles.trackingTitle}>Биометрические данные</Text>
              <Text style={styles.trackingDescription}>
                Пульс, HRV, стресс, настроение
              </Text>
            </View>
            <Switch
              value={biometricTracking}
              onValueChange={setBiometricTracking}
              trackColor={{ false: '#767577', true: '#4CAF50' }}
              thumbColor={biometricTracking ? '#fff' : '#f4f3f4'}
            />
          </View>

          <View style={styles.trackingOption}>
            <View style={styles.trackingInfo}>
              <Text style={styles.trackingTitle}>GPS отслеживание</Text>
              <Text style={styles.trackingDescription}>
                Маршрут и местоположение
              </Text>
            </View>
            <Switch
              value={gpsTracking}
              onValueChange={setGpsTracking}
              trackColor={{ false: '#767577', true: '#4CAF50' }}
              thumbColor={gpsTracking ? '#fff' : '#f4f3f4'}
            />
          </View>

          <View style={styles.trackingOption}>
            <View style={styles.trackingInfo}>
              <Text style={styles.trackingTitle}>Отслеживание настроения</Text>
              <Text style={styles.trackingDescription}>
                Индекс настроения и эмоции
              </Text>
            </View>
            <Switch
              value={moodTracking}
              onValueChange={setMoodTracking}
              trackColor={{ false: '#767577', true: '#4CAF50' }}
              thumbColor={moodTracking ? '#fff' : '#f4f3f4'}
            />
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Дополнительная информация</Text>
          
          <View style={styles.inputGroup}>
            <Text style={styles.inputLabel}>Название сессии (необязательно)</Text>
            <TextInput
              style={styles.textInput}
              value={sessionName}
              onChangeText={setSessionName}
              placeholder="Например: Утренняя рыбалка"
              placeholderTextColor="#999"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.inputLabel}>Описание (необязательно)</Text>
            <TextInput
              style={[styles.textInput, styles.textArea]}
              value={sessionDescription}
              onChangeText={setSessionDescription}
              placeholder="Дополнительные заметки..."
              placeholderTextColor="#999"
              multiline
              numberOfLines={3}
            />
          </View>
        </View>

        {currentLocation && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Местоположение</Text>
            <View style={styles.locationInfo}>
              <Text style={styles.locationText}>
                📍 Широта: {currentLocation.latitude.toFixed(6)}
              </Text>
              <Text style={styles.locationText}>
                📍 Долгота: {currentLocation.longitude.toFixed(6)}
              </Text>
            </View>
          </View>
        )}
      </View>

      <View style={styles.actions}>
        <TouchableOpacity
          style={styles.cancelButton}
          onPress={handleCancel}
          activeOpacity={0.7}
        >
          <Text style={styles.cancelButtonText}>Отмена</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.startButton, isLoading && styles.startButtonDisabled]}
          onPress={handleStartSession}
          activeOpacity={0.8}
          disabled={isLoading}
        >
          <LinearGradient
            colors={isLoading ? ['#ccc', '#999'] : ['#4CAF50', '#45a049']}
            style={styles.startButtonGradient}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            <Text style={styles.startButtonText}>
              {isLoading ? 'Запуск...' : 'Начать рыбалку'}
            </Text>
          </LinearGradient>
        </TouchableOpacity>
      </View>
    </ScrollView>
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
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
    color: '#fff',
    opacity: 0.9,
  },
  content: {
    padding: 16,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
  },
  handSelection: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  handOption: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 16,
    marginHorizontal: 4,
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
  },
  handOptionSelected: {
    borderColor: '#2196F3',
    backgroundColor: '#E3F2FD',
  },
  handEmoji: {
    fontSize: 32,
    marginBottom: 8,
  },
  handText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 4,
    textAlign: 'center',
  },
  handTextSelected: {
    color: '#2196F3',
  },
  handDescription: {
    fontSize: 12,
    color: '#666',
    textAlign: 'center',
  },
  handDescriptionSelected: {
    color: '#1976D2',
  },
  trackingOption: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#fff',
    padding: 16,
    marginBottom: 8,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 3,
  },
  trackingInfo: {
    flex: 1,
  },
  trackingTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  trackingDescription: {
    fontSize: 14,
    color: '#666',
  },
  inputGroup: {
    marginBottom: 16,
  },
  inputLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  textInput: {
    backgroundColor: '#fff',
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    color: '#333',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  locationInfo: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 3,
  },
  locationText: {
    fontSize: 14,
    color: '#666',
    marginBottom: 4,
  },
  actions: {
    flexDirection: 'row',
    padding: 16,
    justifyContent: 'space-between',
  },
  cancelButton: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 16,
    marginRight: 8,
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#ddd',
  },
  cancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#666',
  },
  startButton: {
    flex: 2,
    borderRadius: 12,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 4.65,
    elevation: 8,
  },
  startButtonDisabled: {
    shadowOpacity: 0.1,
    elevation: 2,
  },
  startButtonGradient: {
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  startButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
  },
});

export default StartSessionScreen;
