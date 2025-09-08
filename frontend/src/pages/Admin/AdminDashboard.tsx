import React, { useState, useEffect } from 'react';
import AdminLayout from '../../components/Admin/AdminLayout';
import StatsCard from '../../components/Admin/StatsCard';
import Icon from '../../components/Icon';
import { adminDashboard, adminRecentActivity } from '../../api';

const AdminDashboard: React.FC = () => {
  const [stats, setStats] = useState<any>(null);
  const [recentActivity, setRecentActivity] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadData = async () => {
      try {
        const [statsData, activityData] = await Promise.all([
          adminDashboard(),
          adminRecentActivity()
        ]);
        
        setStats(statsData.data);
        setRecentActivity(activityData.data);
      } catch (error) {
        console.error('Ошибка загрузки данных:', error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, []);

  if (loading) {
    return (
      <AdminLayout>
        <div className="admin-loading">
          <Icon name="refresh" className="spinning" />
          <span>Загрузка панели управления...</span>
        </div>
      </AdminLayout>
    );
  }

  return (
    <AdminLayout>
      <div className="admin-dashboard">
        <h1>Панель управления</h1>
        
        {/* Statistics Cards */}
        <div className="stats-grid">
          <StatsCard
            title="Всего пользователей"
            value={stats?.users?.total || 0}
            icon="people"
            color="primary"
          />
          <StatsCard
            title="Активных пользователей"
            value={stats?.users?.active || 0}
            icon="person_check"
            color="success"
          />
          <StatsCard
            title="Заблокированных"
            value={stats?.users?.blocked || 0}
            icon="person_off"
            color="danger"
          />
          <StatsCard
            title="Новых сегодня"
            value={stats?.users?.new_today || 0}
            icon="person_add"
            color="info"
          />
        </div>

        <div className="stats-grid">
          <StatsCard
            title="Всего уловов"
            value={stats?.catches?.total || 0}
            icon="fishing"
            color="primary"
          />
          <StatsCard
            title="Активных уловов"
            value={stats?.catches?.active || 0}
            icon="check_circle"
            color="success"
          />
          <StatsCard
            title="Заблокированных уловов"
            value={stats?.catches?.blocked || 0}
            icon="block"
            color="danger"
          />
          <StatsCard
            title="Новых уловов сегодня"
            value={stats?.catches?.new_today || 0}
            icon="add_circle"
            color="info"
          />
        </div>

        <div className="stats-grid">
          <StatsCard
            title="Всего точек"
            value={stats?.points?.total || 0}
            icon="place"
            color="primary"
          />
          <StatsCard
            title="Активных точек"
            value={stats?.points?.active || 0}
            icon="check_circle"
            color="success"
          />
          <StatsCard
            title="Заблокированных точек"
            value={stats?.points?.blocked || 0}
            icon="block"
            color="danger"
          />
          <StatsCard
            title="Новых точек сегодня"
            value={stats?.points?.new_today || 0}
            icon="add_circle"
            color="info"
          />
        </div>

        <div className="stats-grid">
          <StatsCard
            title="Всего жалоб"
            value={stats?.reports?.total || 0}
            icon="report"
            color="primary"
          />
          <StatsCard
            title="Ожидают рассмотрения"
            value={stats?.reports?.pending || 0}
            icon="schedule"
            color="warning"
          />
          <StatsCard
            title="Решено"
            value={stats?.reports?.resolved || 0}
            icon="check_circle"
            color="success"
          />
          <StatsCard
            title="Новых жалоб сегодня"
            value={stats?.reports?.new_today || 0}
            icon="add_circle"
            color="info"
          />
        </div>

        {/* Recent Activity */}
        <div className="admin-section">
          <h2>Последняя активность</h2>
          
          <div className="activity-grid">
            <div className="activity-card">
              <h3>Новые пользователи</h3>
              <div className="activity-list">
                {recentActivity?.recent_users?.map((user: any) => (
                  <div key={user.id} className="activity-item">
                    <div className="activity-item__avatar">
                      <Icon name="person" />
                    </div>
                    <div className="activity-item__content">
                      <div className="activity-item__name">{user.name}</div>
                      <div className="activity-item__meta">
                        @{user.username} • {new Date(user.created_at).toLocaleDateString()}
                      </div>
                    </div>
                    {user.is_blocked && (
                      <div className="activity-item__status blocked">
                        <Icon name="block" />
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>

            <div className="activity-card">
              <h3>Последние уловы</h3>
              <div className="activity-list">
                {recentActivity?.recent_catches?.map((catchRecord: any) => (
                  <div key={catchRecord.id} className="activity-item">
                    <div className="activity-item__avatar">
                      <Icon name="fishing" />
                    </div>
                    <div className="activity-item__content">
                      <div className="activity-item__name">
                        {catchRecord.fish_type} ({catchRecord.weight} кг)
                      </div>
                      <div className="activity-item__meta">
                        {catchRecord.user?.name} • {new Date(catchRecord.created_at).toLocaleDateString()}
                      </div>
                    </div>
                    {catchRecord.is_blocked && (
                      <div className="activity-item__status blocked">
                        <Icon name="block" />
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>

            <div className="activity-card">
              <h3>Последние жалобы</h3>
              <div className="activity-list">
                {recentActivity?.recent_reports?.map((report: any) => (
                  <div key={report.id} className="activity-item">
                    <div className="activity-item__avatar">
                      <Icon name="report" />
                    </div>
                    <div className="activity-item__content">
                      <div className="activity-item__name">
                        {report.reason_label || report.reason}
                      </div>
                      <div className="activity-item__meta">
                        {report.reporter?.name} • {new Date(report.created_at).toLocaleDateString()}
                      </div>
                    </div>
                    <div className={`activity-item__status ${report.status}`}>
                      <Icon name={report.status === 'pending' ? 'schedule' : 'check_circle'} />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
};

export default AdminDashboard;
