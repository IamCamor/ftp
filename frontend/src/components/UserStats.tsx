import React, { useState, useEffect } from 'react';
import Icon from './Icon';
import { getUserStats } from '../api';

interface UserStatsProps {
  userId: number;
  isOwnProfile?: boolean;
}

interface UserStatsData {
  catches_count: number;
  likes_received: number;
  comments_received: number;
  total_weight: number;
  species_count: number;
  top_species?: Array<{ species: string; count: number }>;
}

const UserStats: React.FC<UserStatsProps> = ({ userId, isOwnProfile = false }) => {
  const [stats, setStats] = useState<UserStatsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [activePeriod, setActivePeriod] = useState<'week' | 'month' | 'year'>('month');

  useEffect(() => {
    loadStats();
  }, [userId, activePeriod]);

  const loadStats = async () => {
    try {
      setLoading(true);
      const statsData = await getUserStats(userId, activePeriod);
      // Обеспечиваем безопасную инициализацию данных
      const safeStatsData = {
        catches_count: statsData?.catches_count || 0,
        likes_received: statsData?.likes_received || 0,
        comments_received: statsData?.comments_received || 0,
        total_weight: statsData?.total_weight || 0,
        species_count: statsData?.species_count || 0,
        top_species: statsData?.top_species || []
      };
      setStats(safeStatsData);
    } catch (err) {
      console.error('Failed to load user stats:', err);
      // Устанавливаем значения по умолчанию при ошибке
      setStats({
        catches_count: 0,
        likes_received: 0,
        comments_received: 0,
        total_weight: 0,
        species_count: 0,
        top_species: []
      });
    } finally {
      setLoading(false);
    }
  };

  const getPeriodLabel = (period: string) => {
    switch (period) {
      case 'week': return 'За неделю';
      case 'month': return 'За месяц';
      case 'year': return 'За год';
      default: return 'За месяц';
    }
  };

  if (loading) {
    return (
      <div className="user-stats glass">
        <div className="loading">
          <Icon name="refresh" size="md" />
          <span>Загрузка статистики...</span>
        </div>
      </div>
    );
  }

  if (!stats) {
    return null;
  }

  return (
    <div className="user-stats glass">
      <div className="stats-header">
        <h3>{isOwnProfile ? 'Моя статистика' : 'Статистика'}</h3>
        <div className="period-selector">
          {(['week', 'month', 'year'] as const).map((period) => (
            <button
              key={period}
              className={`period-btn ${activePeriod === period ? 'active' : ''}`}
              onClick={() => setActivePeriod(period)}
            >
              {getPeriodLabel(period)}
            </button>
          ))}
        </div>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-icon">
            <Icon name="fishing" size="md" />
          </div>
          <div className="stat-content">
            <div className="stat-value">{stats.catches_count}</div>
            <div className="stat-label">Уловов</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">
            <Icon name="favorite" size="md" />
          </div>
          <div className="stat-content">
            <div className="stat-value">{stats.likes_received}</div>
            <div className="stat-label">Лайков</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">
            <Icon name="comment" size="md" />
          </div>
          <div className="stat-content">
            <div className="stat-value">{stats.comments_received}</div>
            <div className="stat-label">Комментариев</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">
            <Icon name="scale" size="md" />
          </div>
          <div className="stat-content">
            <div className="stat-value">{stats.total_weight.toFixed(1)}</div>
            <div className="stat-label">кг</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">
            <Icon name="pets" size="md" />
          </div>
          <div className="stat-content">
            <div className="stat-value">{stats.species_count}</div>
            <div className="stat-label">Видов</div>
          </div>
        </div>
      </div>

      {stats.top_species && stats.top_species.length > 0 && (
        <div className="top-species">
          <h4>Популярные виды</h4>
          <div className="species-list">
            {stats.top_species.slice(0, 5).map((species, index) => (
              <div key={species.species} className="species-item">
                <span className="species-rank">#{index + 1}</span>
                <span className="species-name">{species.species}</span>
                <span className="species-count">{species.count}</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default UserStats;
