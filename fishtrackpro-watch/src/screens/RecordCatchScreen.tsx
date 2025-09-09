import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ScrollView,
  Alert,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../types';
import { apiService } from '../services/api';
import { biometricService } from '../services/biometricService';
import { formatWeight, formatLength, formatNumber } from '../utils/helpers';

type RecordCatchScreenNavigationProp = StackNavigationProp<RootStackParamList, 'RecordCatch'>;
type RecordCatchScreenRouteProp = RouteProp<RootStackParamList, 'RecordCatch'>;

const { width } = Dimensions.get('window');

const RecordCatchScreen: React.FC = () => {
  const navigation = useNavigation<RecordCatchScreenNavigationProp>();
  const route = useRoute<RecordCatchScreenRouteProp>();
  const { sessionId } = route.params;

  const [fishSpecies, setFishSpecies] = useState('');
  const [weight, setWeight] = useState('');
  const [length, setLength] = useState('');
  const [notes, setNotes] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [currentBiometric, setCurrentBiometric] = useState<any>(null);

  const commonFishSpecies = [
    'Щука', 'Окунь', 'Лещ', 'Плотва', 'Карась', 'Карп', 'Сазан',
    'Сом', 'Судак', 'Жерех', 'Голавль', 'Язь', 'Линь', 'Красноперка'
  ];

  useEffect(() => {
    getCurrentBiometricData();
  }, []);

  const getCurrentBiometricData = async () => {
    try {
      const sensors = await biometricService.collectBiometricData();
      setCurrentBiometric({
        heart_rate: sensors.heartRate,
        hrv: sensors.hrv,
        stress_level: sensors.heartRate ? calculateStressLevel(sensors.heartRate, sensors.hrv) : null,
      });
    } catch (error) {
      console.error('Error getting biometric data:', error);
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

  const handleRecordCatch = async () => {
    if (!fishSpecies.trim()) {
      Alert.alert('Ошибка', 'Пожалуйста, укажите вид рыбы');
      return;
    }

    if (isLoading) return;

    try {
      setIsLoading(true);

      const catchData = {
        session_id: sessionId,
        fish_species: fishSpecies.trim(),
        weight: weight ? parseFloat(weight) : undefined,
        length: length ? parseFloat(length) : undefined,
        notes: notes.trim() || undefined,
        heart_rate: currentBiometric?.heart_rate,
        hrv: currentBiometric?.hrv,
        stress_level: currentBiometric?.stress_level,
      };

      const response = await apiService.recordCatch(catchData);

      if (response.success && response.data) {
        Alert.alert(
          'Улов записан!',
          `Поздравляем с поимкой ${response.data.fish_species}!\n\n` +
          `В этот момент вы были ${response.data.biometric_data.mood_description.toLowerCase()}\n` +
          `Пульс: ${response.data.biometric_data.heart_rate} уд/мин`,
          [
            {
              text: 'OK',
              onPress: () => navigation.goBack(),
            },
          ]
        );
      } else {
        Alert.alert('Ошибка', response.message || 'Не удалось записать улов');
      }
    } catch (error) {
      console.error('Error recording catch:', error);
      Alert.alert('Ошибка', 'Не удалось записать улов');
    } finally {
      setIsLoading(false);
    }
  };

  const handleCancel = () => {
    navigation.goBack();
  };

  const getMoodEmoji = (moodIndex: number): string => {
    if (moodIndex >= 80) return '😊';
    if (moodIndex >= 60) return '🙂';
    if (moodIndex >= 40) return '😐';
    if (moodIndex >= 20) return '😕';
    return '😩';
  };

  const getMoodDescription = (moodIndex: number): string => {
    if (moodIndex >= 80) return 'Отличное настроение';
    if (moodIndex >= 60) return 'Хорошее настроение';
    if (moodIndex >= 40) return 'Нейтральное настроение';
    if (moodIndex >= 20) return 'Плохое настроение';
    return 'Очень плохое настроение';
  };

  const calculateMoodIndex = (): number => {
    if (!currentBiometric) return 50;
    
    let moodScore = 50;
    
    if (currentBiometric.heart_rate) {
      if (currentBiometric.heart_rate >= 60 && currentBiometric.heart_rate <= 100) {
        moodScore += 15;
      } else if (currentBiometric.heart_rate > 140) {
        moodScore += 20; // Excitement from catching fish
      } else if (currentBiometric.heart_rate > 100) {
        moodScore += 10;
      }
    }
    
    if (currentBiometric.hrv) {
      if (currentBiometric.hrv > 50) {
        moodScore += 20;
      } else if (currentBiometric.hrv < 20) {
        moodScore -= 15;
      }
    }
    
    // Bonus for catching fish
    moodScore += 30;
    
    return Math.max(0, Math.min(100, moodScore));
  };

  const moodIndex = calculateMoodIndex();
  const moodEmoji = getMoodEmoji(moodIndex);
  const moodDescription = getMoodDescription(moodIndex);

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      <LinearGradient
        colors={['#FF5722', '#E64A19']}
        style={styles.header}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <Text style={styles.title}>🎣 Поймал рыбу!</Text>
        <Text style={styles.subtitle}>Запишите детали улова</Text>
      </LinearGradient>

      <View style={styles.content}>
        {currentBiometric && (
          <View style={styles.biometricMoment}>
            <Text style={styles.biometricTitle}>Момент поимки:</Text>
            <View style={styles.biometricInfo}>
              <Text style={styles.biometricEmoji}>{moodEmoji}</Text>
              <View style={styles.biometricDetails}>
                <Text style={styles.biometricMood}>{moodDescription}</Text>
                <Text style={styles.biometricHeartRate}>
                  Пульс: {currentBiometric.heart_rate || '--'} уд/мин
                </Text>
                <Text style={styles.biometricStress}>
                  Стресс: {formatNumber(currentBiometric.stress_level || 0)}%
                </Text>
              </View>
            </View>
          </View>
        )}

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Вид рыбы *</Text>
          <View style={styles.fishSpeciesGrid}>
            {commonFishSpecies.map((species) => (
              <TouchableOpacity
                key={species}
                style={[
                  styles.fishSpeciesButton,
                  fishSpecies === species && styles.fishSpeciesButtonSelected,
                ]}
                onPress={() => setFishSpecies(species)}
                activeOpacity={0.7}
              >
                <Text style={[
                  styles.fishSpeciesText,
                  fishSpecies === species && styles.fishSpeciesTextSelected,
                ]}>
                  {species}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
          
          <TextInput
            style={styles.textInput}
            value={fishSpecies}
            onChangeText={setFishSpecies}
            placeholder="Или введите свой вариант"
            placeholderTextColor="#999"
          />
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Размеры (необязательно)</Text>
          
          <View style={styles.sizeRow}>
            <View style={styles.sizeInput}>
              <Text style={styles.inputLabel}>Вес (кг)</Text>
              <TextInput
                style={styles.textInput}
                value={weight}
                onChangeText={setWeight}
                placeholder="0.0"
                placeholderTextColor="#999"
                keyboardType="numeric"
              />
            </View>
            
            <View style={styles.sizeInput}>
              <Text style={styles.inputLabel}>Длина (см)</Text>
              <TextInput
                style={styles.textInput}
                value={length}
                onChangeText={setLength}
                placeholder="0"
                placeholderTextColor="#999"
                keyboardType="numeric"
              />
            </View>
          </View>
        </View>

        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Заметки (необязательно)</Text>
          <TextInput
            style={[styles.textInput, styles.textArea]}
            value={notes}
            onChangeText={setNotes}
            placeholder="Дополнительная информация о поимке..."
            placeholderTextColor="#999"
            multiline
            numberOfLines={3}
          />
        </View>

        <View style={styles.preview}>
          <Text style={styles.previewTitle}>Предварительный просмотр:</Text>
          <View style={styles.previewContent}>
            <Text style={styles.previewText}>
              🐟 {fishSpecies || 'Вид рыбы'}
            </Text>
            {weight && (
              <Text style={styles.previewText}>
                ⚖️ {formatWeight(parseFloat(weight))}
              </Text>
            )}
            {length && (
              <Text style={styles.previewText}>
                📏 {formatLength(parseFloat(length))}
              </Text>
            )}
            {notes && (
              <Text style={styles.previewText}>
                📝 {notes}
              </Text>
            )}
          </View>
        </View>
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
          style={[styles.recordButton, isLoading && styles.recordButtonDisabled]}
          onPress={handleRecordCatch}
          activeOpacity={0.8}
          disabled={isLoading}
        >
          <LinearGradient
            colors={isLoading ? ['#ccc', '#999'] : ['#4CAF50', '#45a049']}
            style={styles.recordButtonGradient}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            <Text style={styles.recordButtonText}>
              {isLoading ? 'Запись...' : 'Записать улов'}
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
  biometricMoment: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 20,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
  },
  biometricTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  biometricInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  biometricEmoji: {
    fontSize: 32,
    marginRight: 16,
  },
  biometricDetails: {
    flex: 1,
  },
  biometricMood: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
    marginBottom: 4,
  },
  biometricHeartRate: {
    fontSize: 14,
    color: '#666',
    marginBottom: 2,
  },
  biometricStress: {
    fontSize: 14,
    color: '#666',
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  fishSpeciesGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 12,
  },
  fishSpeciesButton: {
    backgroundColor: '#fff',
    paddingHorizontal: 12,
    paddingVertical: 8,
    margin: 4,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  fishSpeciesButtonSelected: {
    backgroundColor: '#2196F3',
    borderColor: '#2196F3',
  },
  fishSpeciesText: {
    fontSize: 14,
    color: '#333',
  },
  fishSpeciesTextSelected: {
    color: '#fff',
    fontWeight: 'bold',
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
  sizeRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  sizeInput: {
    flex: 1,
    marginHorizontal: 4,
  },
  inputLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  preview: {
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 20,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 5,
  },
  previewTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 12,
  },
  previewContent: {
    gap: 8,
  },
  previewText: {
    fontSize: 16,
    color: '#333',
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
  recordButton: {
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
  recordButtonDisabled: {
    shadowOpacity: 0.1,
    elevation: 2,
  },
  recordButtonGradient: {
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  recordButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
  },
});

export default RecordCatchScreen;
