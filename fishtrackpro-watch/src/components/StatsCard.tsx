import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { 
  formatNumber, 
  formatDuration,
  getMoodEmoji,
  getMoodColor
} from '../utils/helpers';
import { BiometricStats } from '../types';

interface StatsCardProps {
  stats: BiometricStats;
  title?: string;
  showDetails?: boolean;
}

const { width } = Dimensions.get('window');

const StatsCard: React.FC<StatsCardProps> = ({
  stats,
  title = 'Статистика',
  showDetails = true,
}) => {
  const moodEmoji = getMoodEmoji(stats.avg_mood_index);
  const moodColor = getMoodColor(stats.avg_mood_index);

  const getTrendEmoji = (trend: string): string => {
    switch (trend) {
      case 'improving': return '📈';
      case 'declining': return '📉';
      default: return '➡️';
    }
  };

  const getTrendText = (trend: string): string => {
    switch (trend) {
      case 'improving': return 'Улучшается';
      case 'declining': return 'Ухудшается';
      default: return 'Стабильно';
    }
  };

  const getTrendColor = (trend: string): string => {
    switch (trend) {
      case 'improving': return '#4CAF50';
      case 'declining': return '#F44336';
      default: return '#FF9800';
    }
  };

  return (
    <LinearGradient
      colors={[moodColor, lightenColor(moodColor, 0.3)]}
      style={styles.container}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
    >
      <View style={styles.header}>
        <Text style={styles.moodEmoji}>{moodEmoji}</Text>
        <View style={styles.titleInfo}>
          <Text style={styles.title}>{title}</Text>
          <Text style={styles.subtitle}>
            {stats.total_sessions} сессий • {formatDuration(stats.total_fishing_time)}
          </Text>
        </View>
      </View>

      <View style={styles.mainStats}>
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{formatNumber(stats.avg_mood_index)}</Text>
          <Text style={styles.statLabel}>Среднее настроение</Text>
        </View>
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{stats.avg_heart_rate}</Text>
          <Text style={styles.statLabel}>Средний пульс</Text>
        </View>
        <View style={styles.statItem}>
          <Text style={styles.statValue}>{formatNumber(stats.avg_stress_level)}%</Text>
          <Text style={styles.statLabel}>Средний стресс</Text>
        </View>
      </View>

      {showDetails && (
        <View style={styles.details}>
          <View style={styles.detailRow}>
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Тренд настроения</Text>
              <View style={styles.trendContainer}>
                <Text style={styles.trendEmoji}>{getTrendEmoji(stats.mood_trend)}</Text>
                <Text style={[styles.trendText, { color: getTrendColor(stats.mood_trend) }]}>
                  {getTrendText(stats.mood_trend)}
                </Text>
              </View>
            </View>
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Лучшая сессия</Text>
              <Text style={styles.detailValue}>
                {formatNumber(stats.best_session_mood)}
              </Text>
            </View>
          </View>

          <View style={styles.detailRow}>
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Худшая сессия</Text>
              <Text style={styles.detailValue}>
                {formatNumber(stats.worst_session_mood)}
              </Text>
            </View>
            <View style={styles.detailItem}>
              <Text style={styles.detailLabel}>Общее время</Text>
              <Text style={styles.detailValue}>
                {formatDuration(stats.total_fishing_time)}
              </Text>
            </View>
          </View>
        </View>
      )}

      <View style={styles.footer}>
        <Text style={styles.footerText}>
          Обновлено: {new Date().toLocaleDateString('ru-RU')}
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
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  moodEmoji: {
    fontSize: 40,
    marginRight: 16,
  },
  titleInfo: {
    flex: 1,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
  },
  mainStats: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 20,
  },
  statItem: {
    alignItems: 'center',
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  statLabel: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.8,
    textAlign: 'center',
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
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
  },
  trendContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  trendEmoji: {
    fontSize: 16,
    marginRight: 4,
  },
  trendText: {
    fontSize: 14,
    fontWeight: 'bold',
  },
  footer: {
    alignItems: 'center',
  },
  footerText: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.8,
  },
});

export default StatsCard;
