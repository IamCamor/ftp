import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { 
  getMoodEmoji, 
  getMoodDescription, 
  getMoodColor,
  getHeartRateZoneColor,
  getHeartRateZoneDescription,
  getStressColor,
  getStressDescription,
  formatNumber
} from '../utils/helpers';
import { BiometricData } from '../types';

interface BiometricCardProps {
  data: BiometricData;
  showDetails?: boolean;
  compact?: boolean;
}

const { width } = Dimensions.get('window');

const BiometricCard: React.FC<BiometricCardProps> = ({
  data,
  showDetails = true,
  compact = false,
}) => {
  const moodEmoji = getMoodEmoji(data.mood_index || 50);
  const moodDescription = getMoodDescription(data.mood_index || 50);
  const moodColor = getMoodColor(data.mood_index || 50);
  
  const heartRateZone = data.heart_rate ? 
    getHeartRateZoneDescription(getHeartRateZone(data.heart_rate)) : 'Неизвестно';
  const heartRateColor = data.heart_rate ? 
    getHeartRateZoneColor(getHeartRateZone(data.heart_rate)) : '#757575';
  
  const stressColor = getStressColor(data.stress_level || 50);
  const stressDescription = getStressDescription(data.stress_level || 50);

  if (compact) {
    return (
      <View style={styles.compactContainer}>
        <View style={styles.compactRow}>
          <View style={styles.compactItem}>
            <Text style={styles.compactEmoji}>{moodEmoji}</Text>
            <Text style={styles.compactValue}>{formatNumber(data.mood_index || 0)}</Text>
          </View>
          <View style={styles.compactItem}>
            <Text style={[styles.compactIcon, { color: heartRateColor }]}>❤️</Text>
            <Text style={styles.compactValue}>{data.heart_rate || '--'}</Text>
          </View>
          <View style={styles.compactItem}>
            <Text style={[styles.compactIcon, { color: stressColor }]}>⚡</Text>
            <Text style={styles.compactValue}>{formatNumber(data.stress_level || 0)}</Text>
          </View>
        </View>
      </View>
    );
  }

  return (
    <LinearGradient
      colors={[moodColor, lightenColor(moodColor, 0.3)]}
      style={styles.container}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
    >
      <View style={styles.header}>
        <Text style={styles.moodEmoji}>{moodEmoji}</Text>
        <View style={styles.moodInfo}>
          <Text style={styles.moodIndex}>{formatNumber(data.mood_index || 0)}</Text>
          <Text style={styles.moodDescription}>{moodDescription}</Text>
        </View>
      </View>

      {showDetails && (
        <View style={styles.details}>
          <View style={styles.detailRow}>
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Пульс</Text>
              <Text style={[styles.detailValue, { color: heartRateColor }]}>
                {data.heart_rate || '--'} уд/мин
              </Text>
              <Text style={styles.detailSubtext}>{heartRateZone}</Text>
            </View>
            
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Стресс</Text>
              <Text style={[styles.detailValue, { color: stressColor }]}>
                {formatNumber(data.stress_level || 0)}%
              </Text>
              <Text style={styles.detailSubtext}>{stressDescription}</Text>
            </View>
          </View>

          {data.hrv && (
            <View style={styles.detailRow}>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>HRV</Text>
                <Text style={styles.detailValue}>{formatNumber(data.hrv)} мс</Text>
              </View>
              
              {data.activity_level && (
                <View style={styles.detailItem}>
                  <Text style={styles.detailLabel}>Активность</Text>
                  <Text style={styles.detailValue}>{formatNumber(data.activity_level)}%</Text>
                </View>
              )}
            </View>
          )}

          {data.temperature && (
            <View style={styles.detailRow}>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Температура</Text>
                <Text style={styles.detailValue}>{formatNumber(data.temperature)}°C</Text>
              </View>
              
              {data.steps && (
                <View style={styles.detailItem}>
                  <Text style={styles.detailLabel}>Шаги</Text>
                  <Text style={styles.detailValue}>{data.steps}</Text>
                </View>
              )}
            </View>
          )}
        </View>
      )}

      <View style={styles.footer}>
        <Text style={styles.timestamp}>
          {new Date(data.recorded_at).toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit',
          })}
        </Text>
      </View>
    </LinearGradient>
  );
};

const styles = StyleSheet.create({
  container: {
    borderRadius: 16,
    padding: 16,
    marginVertical: 8,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  compactContainer: {
    backgroundColor: '#f5f5f5',
    borderRadius: 12,
    padding: 12,
    marginVertical: 4,
  },
  compactRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
  },
  compactItem: {
    alignItems: 'center',
  },
  compactEmoji: {
    fontSize: 24,
    marginBottom: 4,
  },
  compactIcon: {
    fontSize: 20,
    marginBottom: 4,
  },
  compactValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#333',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  moodEmoji: {
    fontSize: 48,
    marginRight: 16,
  },
  moodInfo: {
    flex: 1,
  },
  moodIndex: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#fff',
  },
  moodDescription: {
    fontSize: 16,
    color: '#fff',
    opacity: 0.9,
  },
  details: {
    marginBottom: 16,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  detailItem: {
    flex: 1,
    alignItems: 'center',
  },
  detailLabel: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.8,
    marginBottom: 4,
  },
  detailValue: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 2,
  },
  detailSubtext: {
    fontSize: 10,
    color: '#fff',
    opacity: 0.7,
  },
  footer: {
    alignItems: 'center',
  },
  timestamp: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.8,
  },
});

export default BiometricCard;
