import React from 'react';
import Icon from '../Icon';

interface CheckboxProps {
  label?: string;
  checked: boolean;
  onChange: (checked: boolean) => void;
  error?: string;
  required?: boolean;
  disabled?: boolean;
  className?: string;
  link?: {
    text: string;
    url: string;
  };
}

const Checkbox: React.FC<CheckboxProps> = ({
  label,
  checked,
  onChange,
  error,
  required = false,
  disabled = false,
  className = '',
  link
}) => {
  return (
    <div className={`form-group checkbox-group ${className}`}>
      <label className="checkbox-label">
        <input
          type="checkbox"
          checked={checked}
          onChange={(e) => onChange(e.target.checked)}
          className={`checkbox-input ${error ? 'error' : ''}`}
          required={required}
          disabled={disabled}
        />
        
        <div className="checkbox-custom">
          {checked && <Icon name="check" size={16} />}
        </div>
        
        <div className="checkbox-text">
          {label}
          {required && <span className="required">*</span>}
          {link && (
            <a href={link.url} target="_blank" rel="noopener noreferrer" className="checkbox-link">
              {link.text}
            </a>
          )}
        </div>
      </label>
      
      {error && <div className="form-error">{error}</div>}
    </div>
  );
};

export default Checkbox;

