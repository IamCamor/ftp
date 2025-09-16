import React from 'react';
import Icon from './Icon';

interface WorkingHoursDisplayProps {
  workingHours?: {
    is_24_7?: boolean;
    schedule?: {
      monday?: { open: string; close: string; closed?: boolean };
      tuesday?: { open: string; close: string; closed?: boolean };
      wednesday?: { open: string; close: string; closed?: boolean };
      thursday?: { open: string; close: string; closed?: boolean };
      friday?: { open: string; close: string; closed?: boolean };
      saturday?: { open: string; close: string; closed?: boolean };
      sunday?: { open: string; close: string; closed?: boolean };
    };
  };
  compact?: boolean;
}

const WorkingHoursDisplay: React.FC<WorkingHoursDisplayProps> = ({
  workingHours,
  compact = false
}) => {
  if (!workingHours) return null;

  const getCurrentDay = () => {
    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    return days[new Date().getDay()];
  };

  const getCurrentStatus = () => {
    if (workingHours.is_24_7) {
      return { isOpen: true, text: 'Круглосуточно', icon: 'schedule' };
    }

    const currentDay = getCurrentDay();
    const todaySchedule = workingHours.schedule?.[currentDay as keyof typeof workingHours.schedule];

    if (!todaySchedule || todaySchedule.closed) {
      return { isOpen: false, text: 'Закрыто', icon: 'schedule' };
    }

    const now = new Date();
    const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    
    const isOpen = currentTime >= todaySchedule.open && currentTime <= todaySchedule.close;
    
    if (isOpen) {
      return { 
        isOpen: true, 
        text: `Открыто до ${todaySchedule.close}`, 
        icon: 'schedule' 
      };
    } else {
      return { 
        isOpen: false, 
        text: `Открыто с ${todaySchedule.open}`, 
        icon: 'schedule' 
      };
    }
  };

  const formatTime = (time: string) => {
    return time.slice(0, 5); // Убираем секунды если есть
  };

  const getScheduleText = () => {
    if (workingHours.is_24_7) {
      return 'Круглосуточно';
    }

    if (!workingHours.schedule) {
      return 'Время работы не указано';
    }

    const days = [
      { key: 'monday', label: 'Пн' },
      { key: 'tuesday', label: 'Вт' },
      { key: 'wednesday', label: 'Ср' },
      { key: 'thursday', label: 'Чт' },
      { key: 'friday', label: 'Пт' },
      { key: 'saturday', label: 'Сб' },
      { key: 'sunday', label: 'Вс' }
    ];

    const scheduleTexts = days.map(day => {
      const daySchedule = workingHours.schedule![day.key as keyof typeof workingHours.schedule];
      
      if (!daySchedule || daySchedule.closed) {
        return `${day.label}: Выходной`;
      }
      
      return `${day.label}: ${formatTime(daySchedule.open)}-${formatTime(daySchedule.close)}`;
    });

    return scheduleTexts.join('\n');
  };

  const currentStatus = getCurrentStatus();

  if (compact) {
    return (
      <div className={`working-hours-compact ${currentStatus.isOpen ? 'open' : 'closed'}`}>
        <Icon name={currentStatus.icon} size="sm" />
        <span>{currentStatus.text}</span>
      </div>
    );
  }

  return (
    <div className="working-hours-display">
      <div className="working-hours-header">
        <Icon name="schedule" size="sm" />
        <span>Время работы</span>
      </div>
      
      <div className={`working-hours-status ${currentStatus.isOpen ? 'open' : 'closed'}`}>
        <Icon name={currentStatus.icon} size="sm" />
        <span>{currentStatus.text}</span>
      </div>
      
      {!workingHours.is_24_7 && workingHours.schedule && (
        <div className="working-hours-schedule">
          <pre>{getScheduleText()}</pre>
        </div>
      )}
    </div>
  );
};

export default WorkingHoursDisplay;

