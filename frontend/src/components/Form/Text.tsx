import React from 'react';
import Icon from '../Icon';

interface TextInputProps {
  label?: string;
  placeholder?: string;
  value: string;
  onChange: (value: string) => void;
  type?: 'text' | 'email' | 'password';
  error?: string;
  required?: boolean;
  disabled?: boolean;
  icon?: string;
  className?: string;
}

const TextInput: React.FC<TextInputProps> = ({
  label,
  placeholder,
  value,
  onChange,
  type = 'text',
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
        
        <input
          type={type}
          placeholder={placeholder}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className={`form-input ${error ? 'error' : ''} ${icon ? 'with-icon' : ''}`}
          required={required}
          disabled={disabled}
        />
      </div>
      
      {error && <div className="form-error">{error}</div>}
    </div>
  );
};

export default TextInput;

