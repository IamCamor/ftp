import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  Dimensions,
} from 'react-native';
import { LineChart } from 'react-native-chart-kit';
import { LinearGradient } from 'react-native-linear-gradient';
import { formatTime, formatNumber } from '../utils/helpers';

interface ChartCardProps {
  title: string;
  data: any[];
  dataKey: string;
  color?: string;
  showDetails?: boolean;
  height?: number;
}

const { width } = Dimensions.get('window');
const chartWidth = width - 32;

const ChartCard: React.FC<ChartCardProps> = ({
  title,
  data,
  dataKey,
  color = '#4CAF50',
  showDetails = true,
  height = 200,
}) => {
  if (!data || data.length === 0) {
    return (
      <View style={styles.emptyContainer}>
        <Text style={styles.emptyText}>Нет данных для отображения</Text>
      </View>
    );
  }

  // Prepare chart data
  const chartData = {
    labels: data.map((item, index) => {
      if (data.length <= 6) {
        return formatTime(item.timestamp || item.recorded_at);
      }
      // Show every nth label for better readability
      const step = Math.ceil(data.length / 6);
      return index % step === 0 ? formatTime(item.timestamp || item.recorded_at) : '';
    }),
    datasets: [
      {
        data: data.map(item => item[dataKey] || 0),
        color: (opacity = 1) => color + Math.floor(opacity * 255).toString(16).padStart(2, '0'),
        strokeWidth: 3,
      },
    ],
  };

  const chartConfig = {
    backgroundColor: color,
    backgroundGradientFrom: color,
    backgroundGradientTo: lightenColor(color, 0.3),
    decimalPlaces: 1,
    color: (opacity = 1) => `rgba(255, 255, 255, ${opacity})`,
    labelColor: (opacity = 1) => `rgba(255, 255, 255, ${opacity})`,
    style: {
      borderRadius: 16,
    },
    propsForDots: {
      r: '4',
      strokeWidth: '2',
      stroke: '#fff',
    },
    propsForBackgroundLines: {
      strokeDasharray: '',
      stroke: 'rgba(255, 255, 255, 0.3)',
    },
  };

  // Calculate statistics
  const values = data.map(item => item[dataKey] || 0);
  const min = Math.min(...values);
  const max = Math.max(...values);
  const avg = values.reduce((sum, val) => sum + val, 0) / values.length;
  const latest = values[values.length - 1];

  return (
    <LinearGradient
      colors={[color, lightenColor(color, 0.3)]}
      style={styles.container}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
    >
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        <Text style={styles.subtitle}>
          {data.length} точек данных
        </Text>
      </View>

      <View style={styles.chartContainer}>
        <LineChart
          data={chartData}
          width={chartWidth}
          height={height}
          chartConfig={chartConfig}
          bezier
          style={styles.chart}
          withDots={data.length <= 20}
          withShadow={false}
          withScrollableDot={false}
          withInnerLines={true}
          withOuterLines={true}
          withVerticalLines={false}
          withHorizontalLines={true}
        />
      </View>

      {showDetails && (
        <View style={styles.stats}>
          <View style={styles.statRow}>
            <View style={styles.statItem}>
              <Text style={styles.statLabel}>Текущее</Text>
              <Text style={styles.statValue}>{formatNumber(latest)}</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statLabel}>Среднее</Text>
              <Text style={styles.statValue}>{formatNumber(avg)}</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statLabel}>Максимум</Text>
              <Text style={styles.statValue}>{formatNumber(max)}</Text>
            </View>
            <View style={styles.statItem}>
              <Text style={styles.statLabel}>Минимум</Text>
              <Text style={styles.statValue}>{formatNumber(min)}</Text>
            </View>
          </View>
        </View>
      )}

      <View style={styles.footer}>
        <Text style={styles.footerText}>
          {data.length > 0 && (
            <>
              Период: {formatTime(data[0].timestamp || data[0].recorded_at)} - {' '}
              {formatTime(data[data.length - 1].timestamp || data[data.length - 1].recorded_at)}
            </>
          )}
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
  emptyContainer: {
    backgroundColor: '#f5f5f5',
    borderRadius: 16,
    padding: 32,
    marginVertical: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyText: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
  },
  header: {
    marginBottom: 16,
  },
  title: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 14,
    color: '#fff',
    opacity: 0.9,
  },
  chartContainer: {
    alignItems: 'center',
    marginBottom: 16,
  },
  chart: {
    borderRadius: 16,
  },
  stats: {
    marginBottom: 16,
  },
  statRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  statItem: {
    alignItems: 'center',
  },
  statLabel: {
    fontSize: 12,
    color: '#fff',
    opacity: 0.8,
    marginBottom: 4,
  },
  statValue: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
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

export default ChartCard;
