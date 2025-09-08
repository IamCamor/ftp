import React from 'react';
import Icon from '../Icon';

interface SelectOption {
  value: string;
  label: string;
}

interface SelectProps {
  label?: string;
  value: string;
  onChange: (value: string) => void;
  options: SelectOption[];
  placeholder?: string;
  error?: string;
  required?: boolean;
  disabled?: boolean;
  icon?: string;
  className?: string;
}

const Select: React.FC<SelectProps> = ({
  label,
  value,
  onChange,
  options,
  placeholder = 'Выберите...',
  error,
  required = false,
  disabled = false,
  icon,
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
        {icon && (
          <div className="input-icon">
            <Icon name={icon} size={20} />
          </div>
        )}
        
        <select
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className={`form-select ${error ? 'error' : ''} ${icon ? 'with-icon' : ''}`}
          required={required}
          disabled={disabled}
        >
          <option value="">{placeholder}</option>
          {options.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        
        <div className="select-arrow">
          <Icon name="keyboard_arrow_down" size={20} />
        </div>
      </div>
      
      {error && <div className="form-error">{error}</div>}
    </div>
  );
};

export default Select;

