import React, { useState, useEffect } from 'react';
import PageLayout from '../components/PageLayout';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import { getRatings } from '../api';
import type { User } from '../types';

interface RatingUser extends User {
  score: number;
  rank: number;
  additional_info?: string;
}

interface RatingsData {
  weekly_catches: RatingUser[];
  monthly_catches: RatingUser[];
  yearly_catches: RatingUser[];
  fishing_days: RatingUser[];
  species_diversity: RatingUser[];
  total_weight: RatingUser[];
}

const RatingsPage: React.FC = () => {
  const [ratings, setRatings] = useState<RatingsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<string>('weekly');

  useEffect(() => {
    loadRatings();
  }, []);

  const loadRatings = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await getRatings();
      setRatings(data);
    } catch (err) {
      console.error('Failed to load ratings:', err);
      setError('Не удалось загрузить рейтинги');
    } finally {
      setLoading(false);
    }
  };

  const getTabData = () => {
    if (!ratings) return [];
    
    switch (activeTab) {
      case 'weekly':
        return ratings.weekly_catches;
      case 'monthly':
        return ratings.monthly_catches;
      case 'yearly':
        return ratings.yearly_catches;
      case 'fishing_days':
        return ratings.fishing_days;
      case 'species':
        return ratings.species_diversity;
      case 'weight':
        return ratings.total_weight;
      default:
        return [];
    }
  };

  const getTabTitle = () => {
    switch (activeTab) {
      case 'weekly':
        return 'За неделю';
      case 'monthly':
        return 'За месяц';
      case 'yearly':
        return 'За год';
      case 'fishing_days':
        return 'Дней на рыбалке';
      case 'species':
        return 'Разнообразие видов';
      case 'weight':
        return 'Общий вес улова';
      default:
        return '';
    }
  };

  const getScoreLabel = (user: RatingUser) => {
    switch (activeTab) {
      case 'weekly':
      case 'monthly':
      case 'yearly':
        return `${user.score} уловов`;
      case 'fishing_days':
        return `${user.score} дней`;
      case 'species':
        return `${user.score} видов`;
      case 'weight':
        return `${user.score} кг`;
      default:
        return user.score.toString();
    }
  };

  const getRankIcon = (rank: number) => {
    switch (rank) {
      case 1:
        return 'emoji_events';
      case 2:
        return 'military_tech';
      case 3:
        return 'workspace_premium';
      default:
        return 'looks_one';
    }
  };

  const getRankColor = (rank: number) => {
    switch (rank) {
      case 1:
        return '#FFD700';
      case 2:
        return '#C0C0C0';
      case 3:
        return '#CD7F32';
      default:
        return 'var(--text-secondary)';
    }
  };

  if (loading) {
    return (
      <PageLayout
        title="Рейтинги"
        description="Рейтинги рыболовов по различным категориям"
        className="screen"
      >
        <div className="page-loading">
          <div className="loading-spinner"></div>
          <p>Загружаем рейтинги...</p>
        </div>
      </PageLayout>
    );
  }

  if (error || !ratings) {
    return (
      <PageLayout
        title="Ошибка"
        description="Не удалось загрузить рейтинги"
        className="screen"
      >
        <div className="page-error">
          <div className="error-content">
            <Icon name="error" size="xl" />
            <h2>Ошибка загрузки</h2>
            <p>{error || 'Рейтинги не найдены'}</p>
            <button className="btn btn-primary" onClick={loadRatings}>
              Попробовать снова
            </button>
          </div>
        </div>
      </PageLayout>
    );
  }

  return (
    <PageLayout
      title="Рейтинги"
      description="Рейтинги рыболовов по различным категориям"
      className="screen"
    >
      <div className="page-header">
        <h1>🏆 Рейтинги рыболовов</h1>
        <p className="page-subtitle">Соревнуйтесь с другими рыбаками и покажите свои достижения</p>
      </div>

      <div className="page-content">
        {/* Tabs */}
        <div className="ratings-tabs">
          <button
            className={`tab-button ${activeTab === 'weekly' ? 'active' : ''}`}
            onClick={() => setActiveTab('weekly')}
          >
            <Icon name="schedule" size="md" />
            <span>Неделя</span>
          </button>
          <button
            className={`tab-button ${activeTab === 'monthly' ? 'active' : ''}`}
            onClick={() => setActiveTab('monthly')}
          >
            <Icon name="calendar_month" size="md" />
            <span>Месяц</span>
          </button>
          <button
            className={`tab-button ${activeTab === 'yearly' ? 'active' : ''}`}
            onClick={() => setActiveTab('yearly')}
          >
            <Icon name="event" size="md" />
            <span>Год</span>
          </button>
          <button
            className={`tab-button ${activeTab === 'fishing_days' ? 'active' : ''}`}
            onClick={() => setActiveTab('fishing_days')}
          >
            <Icon name="calendar_today" size="md" />
            <span>Дни</span>
          </button>
          <button
            className={`tab-button ${activeTab === 'species' ? 'active' : ''}`}
            onClick={() => setActiveTab('species')}
          >
            <Icon name="pets" size="md" />
            <span>Виды</span>
          </button>
          <button
            className={`tab-button ${activeTab === 'weight' ? 'active' : ''}`}
            onClick={() => setActiveTab('weight')}
          >
            <Icon name="scale" size="md" />
            <span>Вес</span>
          </button>
        </div>

        {/* Rating List */}
        <div className="ratings-content">
          <div className="rating-header">
            <h2>{getTabTitle()}</h2>
            <div className="rating-stats">
              <span>Участников: {getTabData().length}</span>
            </div>
          </div>

          <div className="rating-list">
            {getTabData().length === 0 ? (
              <div className="no-data">
                <Icon name="leaderboard" size="xl" />
                <h3>Пока нет данных</h3>
                <p>В этой категории пока нет участников</p>
              </div>
            ) : (
              getTabData().map((user, index) => (
                <div key={user.id} className={`rating-item ${index < 3 ? 'top-three' : ''}`}>
                  <div className="rank-info">
                    <div 
                      className="rank-icon"
                      style={{ color: getRankColor(user.rank) }}
                    >
                      <Icon name={getRankIcon(user.rank)} size="md" />
                    </div>
                    <div className="rank-number">
                      #{user.rank}
                    </div>
                  </div>

                  <div className="user-info">
                    <Avatar 
                      src={user.photo_url} 
                      size="xl"
                      crownIconUrl={user.crown_icon_url}
                      isPremium={user.is_premium}
                      name={user.name}
                    />
                    <div className="user-details">
                      <h3 className="user-name">{user.name}</h3>
                      <p className="user-username">@{user.username}</p>
                      {user.additional_info && (
                        <p className="user-additional">{user.additional_info}</p>
                      )}
                    </div>
                  </div>

                  <div className="score-info">
                    <div className="score-value">{getScoreLabel(user)}</div>
                    {index < 3 && (
                      <div className="medal">
                        <Icon name={getRankIcon(user.rank)} size="sm" />
                      </div>
                    )}
                  </div>
                </div>
              ))
            )}
          </div>
        </div>

        {/* Info Section */}
        <div className="ratings-info">
          <div className="info-card">
            <Icon name="info" size="md" />
            <div>
              <h3>Как формируются рейтинги?</h3>
              <p>Рейтинги обновляются ежедневно на основе ваших уловов и активности на платформе</p>
            </div>
          </div>
        </div>
      </div>
    </PageLayout>
  );
};

export default RatingsPage;

