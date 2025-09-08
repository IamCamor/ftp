import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import { events } from '../api';
import type { Event } from '../types';

const EventsPage: React.FC = () => {
  const navigate = useNavigate();
  const [eventsList, setEventsList] = useState<Event[]>([]);
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

  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка мероприятий...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="screen">
        <div className="error">
          <p>{error}</p>
          <button onClick={loadEvents} className="btn btn-primary">
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="screen">
      <div className="events-header">
        <h2>Мероприятия</h2>
        <button className="btn btn-primary" onClick={handleCreateEvent}>
          <Icon name="add" size={20} />
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
                  <Icon name="schedule" size={16} />
                  <span>{formatDate(event.start_at)}</span>
                </div>
              </div>

              {event.description && (
                <p className="event-description">{event.description}</p>
              )}

              <div className="event-meta">
                <div className="event-organizer">
                  <Avatar src={event.organizer?.photo_url} size={24} />
                  <span>Организатор: {event.organizer?.name}</span>
                </div>
                
                {event.location_name && (
                  <div className="event-location">
                    <Icon name="location_on" size={16} />
                    <span>{event.location_name}</span>
                  </div>
                )}

                <div className="event-stats">
                  <Icon name="group" size={16} />
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
    </div>
  );
};

export default EventsPage;

