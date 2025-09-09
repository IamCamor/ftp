import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
} from 'react-native';
import { LinearGradient } from 'react-native-linear-gradient';
import { 
  formatDuration, 
  formatDateTime, 
  getMoodEmoji, 
  getMoodColor,
  formatNumber,
  formatWeight
} from '../utils/helpers';
import { FishingSession } from '../types';

interface SessionCardProps {
  session: FishingSession;
  onPress?: () => void;
  showDetails?: boolean;
  compact?: boolean;
}

const SessionCard: React.FC<SessionCardProps> = ({
  session,
  onPress,
  showDetails = true,
  compact = false,
}) => {
  const moodEmoji = getMoodEmoji(session.avg_mood_index || 50);
  const moodColor = getMoodColor(session.avg_mood_index || 50);
  
  const getStatusColor = (status: string): string => {
    switch (status) {
      case 'active': return '#4CAF50';
      case 'paused': return '#FF9800';
      case 'completed': return '#2196F3';
      case 'cancelled': return '#F44336';
      default: return '#757575';
    }
  };

  const getStatusText = (status: string): string => {
    switch (status) {
      case 'active': return 'Активная';
      case 'paused': return 'Приостановлена';
      case 'completed': return 'Завершена';
      case 'cancelled': return 'Отменена';
      default: return 'Неизвестно';
    }
  };

  const getWatchHandText = (hand?: string): string => {
    switch (hand) {
      case 'casting': return 'На руке заброса';
      case 'reeling': return 'На руке сматывания';
      default: return 'Не указано';
    }
  };

  if (compact) {
    return (
      <TouchableOpacity 
        style={[styles.compactContainer, { borderLeftColor: getStatusColor(session.status) }]}
        onPress={onPress}
        activeOpacity={0.7}
      >
        <View style={styles.compactHeader}>
          <Text style={styles.compactEmoji}>{moodEmoji}</Text>
          <View style={styles.compactInfo}>
            <Text style={styles.compactTitle}>
              {session.name || 'Сессия рыбалки'}
            </Text>
            <Text style={styles.compactSubtitle}>
              {formatDateTime(session.started_at)} • {formatDuration(session.duration)}
            </Text>
          </View>
          <Text style={[styles.compactStatus, { color: getStatusColor(session.status) }]}>
            {getStatusText(session.status)}
          </Text>
        </View>
      </TouchableOpacity>
    );
  }

  return (
    <TouchableOpacity onPress={onPress} activeOpacity={0.8}>
      <LinearGradient
        colors={[moodColor, lightenColor(moodColor, 0.3)]}
        style={styles.container}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        <View style={styles.header}>
          <View style={styles.titleRow}>
            <Text style={styles.moodEmoji}>{moodEmoji}</Text>
            <View style={styles.titleInfo}>
              <Text style={styles.title}>
                {session.name || 'Сессия рыбалки'}
              </Text>
              <Text style={styles.subtitle}>
                {formatDateTime(session.started_at)}
              </Text>
            </View>
            <View style={[styles.statusBadge, { backgroundColor: getStatusColor(session.status) }]}>
              <Text style={styles.statusText}>{getStatusText(session.status)}</Text>
            </View>
          </View>
        </View>

        {showDetails && (
          <View style={styles.details}>
            <View style={styles.detailRow}>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Длительность</Text>
                <Text style={styles.detailValue}>{formatDuration(session.duration)}</Text>
              </View>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Настроение</Text>
                <Text style={styles.detailValue}>
                  {formatNumber(session.avg_mood_index || 0)}
                </Text>
              </View>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Пульс</Text>
                <Text style={styles.detailValue}>
                  {session.avg_heart_rate || '--'} уд/мин
                </Text>
              </View>
            </View>

            <View style={styles.detailRow}>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Забросы</Text>
                <Text style={styles.detailValue}>{session.total_casts}</Text>
              </View>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Обороты</Text>
                <Text style={styles.detailValue}>{session.total_reels}</Text>
              </View>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Метры</Text>
                <Text style={styles.detailValue}>
                  {formatNumber(session.total_reels_meters, 0)}
                </Text>
              </View>
            </View>

            <View style={styles.detailRow}>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Уловы</Text>
                <Text style={styles.detailValue}>{session.catches_count}</Text>
              </View>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Вес</Text>
                <Text style={styles.detailValue}>
                  {formatWeight(session.total_weight)}
                </Text>
              </View>
              <View style={styles.detailItem}>
                <Text style={styles.detailLabel}>Рука</Text>
                <Text style={styles.detailValue}>
                  {getWatchHandText(session.watch_hand)}
                </Text>
              </View>
            </View>

            {session.mood_breakdown && (
              <View style={styles.moodBreakdown}>
                <Text style={styles.breakdownTitle}>Распределение настроения:</Text>
                <View style={styles.breakdownRow}>
                  <View style={styles.breakdownItem}>
                    <Text style={styles.breakdownLabel}>😊 Высокое</Text>
                    <Text style={styles.breakdownValue}>
                      {session.mood_breakdown.high}%
                    </Text>
                  </View>
                  <View style={styles.breakdownItem}>
                    <Text style={styles.breakdownLabel}>😐 Среднее</Text>
                    <Text style={styles.breakdownValue}>
                      {session.mood_breakdown.medium}%
                    </Text>
                  </View>
                  <View style={styles.breakdownItem}>
                    <Text style={styles.breakdownLabel}>😩 Низкое</Text>
                    <Text style={styles.breakdownValue}>
                      {session.mood_breakdown.low}%
                    </Text>
                  </View>
                </View>
              </View>
            )}

            {session.session_summary && (
              <View style={styles.summary}>
                <Text style={styles.summaryText}>{session.session_summary}</Text>
              </View>
            )}
          </View>
        )}

        <View style={styles.footer}>
          <Text style={styles.footerText}>
            {session.ended_at ? 
              `Завершена: ${formatDateTime(session.ended_at)}` :
              'В процессе'
            }
          </Text>
        </View>
      </LinearGradient>
    </TouchableOpacity>
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
    borderLeftWidth: 4,
  },
  compactHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  compactEmoji: {
    fontSize: 24,
    marginRight: 12,
  },
  compactInfo: {
    flex: 1,
  },
  compactTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 2,
  },
  compactSubtitle: {
    fontSize: 12,
    color: '#666',
  },
  compactStatus: {
    fontSize: 12,
    fontWeight: 'bold',
  },
  header: {
    marginBottom: 16,
  },
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  moodEmoji: {
    fontSize: 32,
    marginRight: 12,
  },
  titleInfo: {
    flex: 1,
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 2,
  },
  subtitle: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#fff',
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
  moodBreakdown: {
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.3)',
  },
  breakdownTitle: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
    marginBottom: 8,
    textAlign: 'center',
  },
  breakdownRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  breakdownItem: {
    alignItems: 'center',
  },
  breakdownLabel: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.8,
    marginBottom: 2,
  },
  breakdownValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#fff',
  },
  summary: {
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.3)',
  },
  summaryText: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
    textAlign: 'center',
    fontStyle: 'italic',
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

export default SessionCard;
