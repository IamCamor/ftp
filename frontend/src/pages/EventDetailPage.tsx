import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { eventById, joinEvent, leaveEvent } from '../api';
import { Event } from '../types';
import ScreenLayout from '../components/ScreenLayout';
import Icon from '../components/Icon';
import Avatar from '../components/Avatar';

const EventDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [event, setEvent] = useState<Event | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [joining, setJoining] = useState(false);

  useEffect(() => {
    if (id) {
      loadEvent();
    }
  }, [id]);

  const loadEvent = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await eventById(Number(id));
      setEvent(data);
    } catch (err) {
      console.error('Failed to load event:', err);
      setError('Не удалось загрузить мероприятие');
    } finally {
      setLoading(false);
    }
  };

  const handleJoinEvent = async () => {
    if (!event || joining) return;

    try {
      setJoining(true);
      await joinEvent(event.id);
      // Reload event to get updated participant status
      await loadEvent();
    } catch (err: any) {
      console.error('Failed to join event:', err);
      if (err.message?.includes('Already participating')) {
        // User is already participating, just reload the event
        await loadEvent();
      } else {
        alert('Не удалось присоединиться к мероприятию');
      }
    } finally {
      setJoining(false);
    }
  };

  const handleLeaveEvent = async () => {
    if (!event || joining) return;

    try {
      setJoining(true);
      await leaveEvent(event.id);
      // Reload event to get updated participant status
      await loadEvent();
    } catch (err) {
      console.error('Failed to leave event:', err);
      alert('Не удалось покинуть мероприятие');
    } finally {
      setJoining(false);
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const formatLocation = () => {
    if (event?.location_name) {
      return event.location_name;
    }
    if (event?.lat && event?.lng) {
      return `${event.lat.toFixed(6)}, ${event.lng.toFixed(6)}`;
    }
    return 'Место не указано';
  };

  if (loading) {
    return (
      <ScreenLayout>
        <div className="loading">Загрузка мероприятия...</div>
      </ScreenLayout>
    );
  }

  if (error || !event) {
    return (
      <ScreenLayout>
        <div className="error-message">
          <p>{error || 'Мероприятие не найдено'}</p>
          <button className="btn btn-primary" onClick={() => navigate('/events')}>
            Вернуться к мероприятиям
          </button>
        </div>
      </ScreenLayout>
    );
  }

  return (
    <ScreenLayout>
      <div className="event-detail">
        {/* Cover Image */}
        {event.cover_url && (
          <div className="event-cover">
            <img src={event.cover_url} alt={event.title} />
          </div>
        )}

        {/* Event Header */}
        <div className="event-header">
          <h1>{event.title}</h1>
          <div className="event-meta">
            <div className="event-date">
              <Icon name="schedule" size="md" />
              <span>{formatDate(event.start_at)}</span>
            </div>
            {event.end_at && (
              <div className="event-end-date">
                <Icon name="event" size="md" />
                <span>До: {formatDate(event.end_at)}</span>
              </div>
            )}
            <div className="event-location">
              <Icon name="place" size="md" />
              <span>{formatLocation()}</span>
            </div>
          </div>
        </div>

        {/* Event Description */}
        {event.description && (
          <div className="event-description">
            <h3>Описание</h3>
            <p>{event.description}</p>
          </div>
        )}

        {/* Organizer */}
        {event.organizer && (
          <div className="event-organizer">
            <h3>Организатор</h3>
            <div className="organizer-info">
              <Avatar src={event.organizer.photo_url} size="xl" name={event.organizer.name} />
              <div className="organizer-details">
                <div className="organizer-name">{event.organizer.name}</div>
                {event.organizer.username && (
                  <div className="organizer-username">@{event.organizer.username}</div>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Participants */}
        <div className="event-participants">
          <h3>
            Участники
            {event.participants_count !== undefined && (
              <span className="participants-count">
                ({event.participants_count}
                {event.max_participants && `/${event.max_participants}`})
              </span>
            )}
          </h3>
          
          {event.participants && event.participants.length > 0 ? (
            <div className="participants-list">
              {event.participants.slice(0, 10).map((participant) => (
                <div key={participant.id} className="participant-item">
                  <Avatar src={participant.photo_url} size="lg" name={participant.name} />
                  <span className="participant-name">{participant.name}</span>
                </div>
              ))}
              {event.participants.length > 10 && (
                <div className="more-participants">
                  +{event.participants.length - 10} еще
                </div>
              )}
            </div>
          ) : (
            <p className="no-participants">Пока нет участников</p>
          )}
        </div>

        {/* Action Buttons */}
        <div className="event-actions">
          {event.is_organizer ? (
            <div className="organizer-actions">
              <button className="btn btn-secondary" disabled>
                <Icon name="edit" size="md" />
                Редактировать
              </button>
            </div>
          ) : event.is_participant ? (
            <button 
              className="btn btn-danger" 
              onClick={handleLeaveEvent}
              disabled={joining}
            >
              <Icon name="exit_to_app" size="md" />
              {joining ? 'Покидаем...' : 'Покинуть мероприятие'}
            </button>
          ) : (
            <button 
              className="btn btn-primary" 
              onClick={handleJoinEvent}
              disabled={joining || !!(event.max_participants && event.participants_count && event.participants_count >= event.max_participants)}
            >
              <Icon name="person_add" size="md" />
              {joining ? 'Присоединяемся...' : 'Присоединиться'}
            </button>
          )}
        </div>
      </div>
    </ScreenLayout>
  );
};

export default EventDetailPage;
