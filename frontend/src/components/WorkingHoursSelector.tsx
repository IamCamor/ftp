import React, { useState } from 'react';
import Icon from './Icon';

interface WorkingHoursSelectorProps {
  value?: {
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
  onChange: (value: any) => void;
}

const WorkingHoursSelector: React.FC<WorkingHoursSelectorProps> = ({
  value = {},
  onChange
}) => {
  const [is24_7, setIs24_7] = useState(value.is_24_7 || false);
  const [schedule, setSchedule] = useState(value.schedule || {});

  const days = [
    { key: 'monday', label: 'Понедельник' },
    { key: 'tuesday', label: 'Вторник' },
    { key: 'wednesday', label: 'Среда' },
    { key: 'thursday', label: 'Четверг' },
    { key: 'friday', label: 'Пятница' },
    { key: 'saturday', label: 'Суббота' },
    { key: 'sunday', label: 'Воскресенье' }
  ];

  const handle24_7Change = (checked: boolean) => {
    setIs24_7(checked);
    onChange({
      is_24_7: checked,
      schedule: checked ? {} : schedule
    });
  };

  const handleDayChange = (dayKey: string, field: 'open' | 'close' | 'closed', newValue: string | boolean) => {
    const newSchedule = { ...schedule };
    
    if (!newSchedule[dayKey as keyof typeof newSchedule]) {
      newSchedule[dayKey as keyof typeof newSchedule] = { open: '09:00', close: '18:00', closed: false };
    }

    if (field === 'closed') {
      newSchedule[dayKey as keyof typeof newSchedule]!.closed = newValue as boolean;
    } else {
      newSchedule[dayKey as keyof typeof newSchedule]![field] = newValue as string;
    }

    setSchedule(newSchedule);
    onChange({
      is_24_7: false,
      schedule: newSchedule
    });
  };

  const formatTime = (time: string) => {
    if (!time) return '09:00';
    return time;
  };

  return (
    <div className="working-hours-selector">
      <div className="working-hours-header">
        <h4>Время работы</h4>
        <div className="working-hours-toggle">
          <label className="toggle-switch">
            <input
              type="checkbox"
              checked={is24_7}
              onChange={(e) => handle24_7Change(e.target.checked)}
            />
            <span className="toggle-slider"></span>
          </label>
          <span className="toggle-label">Круглосуточно</span>
        </div>
      </div>

      {!is24_7 && (
        <div className="working-hours-schedule">
          {days.map((day) => {
            const daySchedule = schedule[day.key as keyof typeof schedule] || { open: '09:00', close: '18:00', closed: false };
            
            return (
              <div key={day.key} className="day-schedule">
                <div className="day-label">
                  <span>{day.label}</span>
                </div>
                
                <div className="day-controls">
                  <label className="closed-checkbox">
                    <input
                      type="checkbox"
                      checked={daySchedule.closed}
                      onChange={(e) => handleDayChange(day.key, 'closed', e.target.checked)}
                    />
                    <span>Выходной</span>
                  </label>
                  
                  {!daySchedule.closed && (
                    <div className="time-inputs">
                      <div className="time-input-group">
                        <label>От</label>
                        <input
                          type="time"
                          value={formatTime(daySchedule.open)}
                          onChange={(e) => handleDayChange(day.key, 'open', e.target.value)}
                          className="time-input"
                        />
                      </div>
                      
                      <div className="time-separator">—</div>
                      
                      <div className="time-input-group">
                        <label>До</label>
                        <input
                          type="time"
                          value={formatTime(daySchedule.close)}
                          onChange={(e) => handleDayChange(day.key, 'close', e.target.value)}
                          className="time-input"
                        />
                      </div>
                    </div>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {is24_7 && (
        <div className="working-hours-24_7">
          <Icon name="schedule" size="md" />
          <span>Место работает круглосуточно</span>
        </div>
      )}
    </div>
  );
};

export default WorkingHoursSelector;

