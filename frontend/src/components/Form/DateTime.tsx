import React from 'react';
import Icon from '../Icon';

interface DateTimeInputProps {
  label?: string;
  value: string;
  onChange: (value: string) => void;
  type?: 'date' | 'datetime-local' | 'time';
  error?: string;
  required?: boolean;
  disabled?: boolean;
  className?: string;
}

const DateTimeInput: React.FC<DateTimeInputProps> = ({
  label,
  value,
  onChange,
  type = 'datetime-local',
  error,
  required = false,
  disabled = false,
  className = ''
}) => {
  return (
    <div className={`form-group ${className}`}>
      {label && (
        <label className="form-label">
          {label}
          {required && <span className="required">*</span>}
        </label>
      )}
      
      <div className="input-wrapper">
        <div className="input-icon">
          <Icon name="schedule" size={20} />
        </div>
        
        <input
          type={type}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className={`form-input with-icon ${error ? 'error' : ''}`}
          required={required}
          disabled={disabled}
        />
      </div>
      
      {error && <div className="form-error">{error}</div>}
    </div>
  );
};

export default DateTimeInput;

