import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import ScreenLayout from '../components/ScreenLayout';
import { events } from '../api';
import type { AppEvent } from '../types';

const EventsPage: React.FC = () => {
  const navigate = useNavigate();
  const [eventsList, setEventsList] = useState<AppEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadEvents();
  }, []);

  const loadEvents = async () => {
    try {
      setLoading(true);
      const data = await events();
      setEventsList(data);
    } catch (err) {
      setError('Не удалось загрузить мероприятия');
      console.error('Events loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleEventClick = (eventId: number) => {
    navigate(`/events/${eventId}`);
  };

  const handleCreateEvent = () => {
    navigate('/events/create');
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
      day: 'numeric',
      month: 'long',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  return (
    <ScreenLayout
      title="Мероприятия"
      description="Рыболовные мероприятия, соревнования и встречи рыбаков"
      keywords={['мероприятия', 'соревнования', 'встречи', 'рыбалка']}
      loading={loading}
      error={error}
      onRetry={loadEvents}
    >
      <div className="events-header">
        <h2>Мероприятия</h2>
        <button className="btn btn-primary" onClick={handleCreateEvent}>
          <Icon name="add" size="md" />
          Создать мероприятие
        </button>
      </div>

      <div className="events-list">
        {eventsList.map((event) => (
          <div 
            key={event.id} 
            className="event-card glass"
            onClick={() => handleEventClick(event.id)}
          >
            {event.cover_url && (
              <div className="event-cover">
                <img src={event.cover_url} alt={event.title} />
              </div>
            )}
            
            <div className="event-content">
              <div className="event-header">
                <h3>{event.title}</h3>
                <div className="event-status">
                  <Icon name="schedule" size="sm" />
                  <span>{formatDate(event.start_at)}</span>
                </div>
              </div>

              {event.description && (
                <p className="event-description">{event.description}</p>
              )}

              <div className="event-meta">
                <div className="event-organizer">
                  <Avatar src={event.organizer?.photo_url} size="md" name={event.organizer?.name} />
                  <span>Организатор: {event.organizer?.name}</span>
                </div>
                
                {event.location_name && (
                  <div className="event-location">
                    <Icon name="location_on" size="sm" />
                    <span>{event.location_name}</span>
                  </div>
                )}

                <div className="event-stats">
                  <Icon name="group" size="sm" />
                  <span>{event.participants_count} участников</span>
                  {event.max_participants && (
                    <span> / {event.max_participants}</span>
                  )}
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </ScreenLayout>
  );
};

export default EventsPage;

