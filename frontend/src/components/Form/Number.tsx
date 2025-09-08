import React from 'react';
import Icon from '../Icon';

interface NumberInputProps {
  label?: string;
  placeholder?: string;
  value: number | '';
  onChange: (value: number | '') => void;
  min?: number;
  max?: number;
  step?: number;
  error?: string;
  required?: boolean;
  disabled?: boolean;
  icon?: string;
  className?: string;
  unit?: string;
}

const NumberInput: React.FC<NumberInputProps> = ({
  label,
  placeholder,
  value,
  onChange,
  min,
  max,
  step = 1,
  error,
  required = false,
  disabled = false,
  icon,
  className = '',
  unit
}) => {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const inputValue = e.target.value;
    if (inputValue === '') {
      onChange('');
    } else {
      const numValue = parseFloat(inputValue);
      if (!isNaN(numValue)) {
        onChange(numValue);
      }
    }
  };

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
        
        <input
          type="number"
          placeholder={placeholder}
          value={value}
          onChange={handleChange}
          min={min}
          max={max}
          step={step}
          className={`form-input ${error ? 'error' : ''} ${icon ? 'with-icon' : ''}`}
          required={required}
          disabled={disabled}
        />
        
        {unit && <span className="input-unit">{unit}</span>}
      </div>
      
      {error && <div className="form-error">{error}</div>}
    </div>
  );
};

export default NumberInput;

