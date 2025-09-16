import React, { useState } from 'react';
import { useLanguage, type Language } from '../hooks/useLanguage';
import Icon from './Icon';

interface LanguageSelectorProps {
  className?: string;
  showLabel?: boolean;
  size?: 'small' | 'medium' | 'large';
}

const LanguageSelector: React.FC<LanguageSelectorProps> = ({ 
  className = '', 
  showLabel = false,
  size = 'medium'
}) => {
  const { language, setLanguage, getCurrentLanguage, languageOptions } = useLanguage();
  const [isOpen, setIsOpen] = useState(false);

  const currentLanguage = getCurrentLanguage();

  const handleLanguageChange = (newLanguage: Language) => {
    setLanguage(newLanguage);
    setIsOpen(false);
  };

  const sizeClasses = {
    small: 'language-selector--small',
    medium: 'language-selector--medium',
    large: 'language-selector--large'
  };

  return (
    <div className={`language-selector ${sizeClasses[size]} ${className}`}>
      <button
        className="language-selector__button"
        onClick={() => setIsOpen(!isOpen)}
        aria-label="Выбрать язык"
        title="Выбрать язык"
      >
        <span className="language-selector__flag">{currentLanguage.flag}</span>
        {showLabel && (
          <span className="language-selector__name">{currentLanguage.nativeName}</span>
        )}
        <Icon 
          name={isOpen ? 'keyboard_arrow_up' : 'keyboard_arrow_down'} 
          size={size === 'small' ? 16 : size === 'large' ? 24 : 20}
        />
      </button>

      {isOpen && (
        <div className="language-selector__dropdown">
          {languageOptions.map((option) => (
            <button
              key={option.code}
              className={`language-selector__option ${
                option.code === language ? 'language-selector__option--active' : ''
              }`}
              onClick={() => handleLanguageChange(option.code)}
            >
              <span className="language-selector__flag">{option.flag}</span>
              <div className="language-selector__info">
                <span className="language-selector__native-name">{option.nativeName}</span>
                <span className="language-selector__english-name">{option.name}</span>
              </div>
              {option.code === language && (
                <Icon name="check" size="md" />
              )}
            </button>
          ))}
        </div>
      )}

      {isOpen && (
        <div 
          className="language-selector__overlay"
          onClick={() => setIsOpen(false)}
        />
      )}
    </div>
  );
};

export default LanguageSelector;

