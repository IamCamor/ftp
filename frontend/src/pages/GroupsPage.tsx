import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import { groups } from '../api';
import type { Group } from '../types';

const GroupsPage: React.FC = () => {
  const navigate = useNavigate();
  const [groupsList, setGroupsList] = useState<Group[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadGroups();
  }, []);

  const loadGroups = async () => {
    try {
      setLoading(true);
      const data = await groups();
      setGroupsList(data);
    } catch (err) {
      setError('Не удалось загрузить группы');
      console.error('Groups loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleGroupClick = (groupId: number) => {
    navigate(`/groups/${groupId}`);
  };

  const handleCreateGroup = () => {
    navigate('/groups/create');
  };

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка групп...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error}</p>
          <button onClick={loadGroups} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="groups-header">
        <h2>Группы рыбаков</h2>
        <button className="btn btn-primary" onClick={handleCreateGroup}>
          <Icon name="add" size={20} />
          Создать группу
        </button>
      </div>

      <div className="groups-list">
        {groupsList.map((group) => (
          <div 
            key={group.id} 
            className="group-card glass"
            onClick={() => handleGroupClick(group.id)}
          >
            {group.cover_url && (
              <div className="group-cover">
                <img src={group.cover_url} alt={group.name} />
              </div>
            )}
            
            <div className="group-content">
              <div className="group-header">
                <h3>{group.name}</h3>
                <div className="group-privacy">
                  <Icon 
                    name={group.privacy === 'public' ? 'public' : 'lock'} 
                    size={16} 
                  />
                  <span>{group.privacy === 'public' ? 'Публичная' : 'Приватная'}</span>
                </div>
              </div>

              {group.description && (
                <p className="group-description">{group.description}</p>
              )}

              <div className="group-meta">
                <div className="group-owner">
                  <Avatar src={group.owner?.photo_url} size={24} />
                  <span>Создатель: {group.owner?.name}</span>
                </div>
                
                <div className="group-stats">
                  <Icon name="group" size={16} />
                  <span>{group.members_count} участников</span>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default GroupsPage;

