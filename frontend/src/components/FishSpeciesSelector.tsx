import React, { useState, useEffect, useRef } from 'react';
import Icon from './Icon';
import { fishSpecies, searchFishSpecies, type FishSpecies } from '../data/fishSpecies';

interface FishSpeciesSelectorProps {
  selectedSpecies: string[];
  onSpeciesChange: (species: string[]) => void;
  placeholder?: string;
  maxSelections?: number;
}

const FishSpeciesSelector: React.FC<FishSpeciesSelectorProps> = ({
  selectedSpecies,
  onSpeciesChange,
  placeholder = "Выберите виды рыб...",
  maxSelections = 10
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [filteredSpecies, setFilteredSpecies] = useState<FishSpecies[]>(fishSpecies);
  const [selectedFish, setSelectedFish] = useState<FishSpecies[]>([]);
  const dropdownRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  // Обновляем отфильтрованные виды при изменении поискового запроса
  useEffect(() => {
    if (searchQuery.trim()) {
      setFilteredSpecies(searchFishSpecies(searchQuery));
    } else {
      setFilteredSpecies(fishSpecies);
    }
  }, [searchQuery]);

  // Обновляем выбранные виды рыб при изменении selectedSpecies
  useEffect(() => {
    const selected = fishSpecies.filter(fish => selectedSpecies.includes(fish.id));
    setSelectedFish(selected);
  }, [selectedSpecies]);

  // Закрываем dropdown при клике вне его
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false);
        setSearchQuery('');
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleToggleSpecies = (fish: FishSpecies) => {
    if (selectedSpecies.includes(fish.id)) {
      // Убираем вид
      const newSelected = selectedSpecies.filter(id => id !== fish.id);
      onSpeciesChange(newSelected);
    } else {
      // Добавляем вид, если не превышен лимит
      if (selectedSpecies.length < maxSelections) {
        const newSelected = [...selectedSpecies, fish.id];
        onSpeciesChange(newSelected);
      }
    }
  };

  const handleRemoveSpecies = (fishId: string) => {
    const newSelected = selectedSpecies.filter(id => id !== fishId);
    onSpeciesChange(newSelected);
  };

  const handleClearAll = () => {
    onSpeciesChange([]);
  };

  const isSelected = (fishId: string) => selectedSpecies.includes(fishId);
  const isMaxReached = selectedSpecies.length >= maxSelections;

  return (
    <div className="fish-species-selector" ref={dropdownRef}>
      <div className="species-input-container">
        <div 
          className="species-input"
          onClick={() => {
            setIsOpen(!isOpen);
            if (!isOpen) {
              setTimeout(() => inputRef.current?.focus(), 100);
            }
          }}
        >
          {selectedFish.length === 0 ? (
            <span className="placeholder">{placeholder}</span>
          ) : (
            <div className="selected-species">
              {selectedFish.map(fish => (
                <span key={fish.id} className="species-tag">
                  {fish.name}
                  <button
                    type="button"
                    className="remove-species"
                    onClick={(e) => {
                      e.stopPropagation();
                      handleRemoveSpecies(fish.id);
                    }}
                  >
                    <Icon name="close" size={12} />
                  </button>
                </span>
              ))}
            </div>
          )}
          <Icon name={isOpen ? "keyboard_arrow_up" : "keyboard_arrow_down"} size="md" />
        </div>
        
        {selectedFish.length > 0 && (
          <button
            type="button"
            className="clear-all-btn"
            onClick={handleClearAll}
            title="Очистить все"
          >
            <Icon name="clear" size="sm" />
          </button>
        )}
      </div>

      {isOpen && (
        <div className="species-dropdown">
          <div className="search-container">
            <input
              ref={inputRef}
              type="text"
              placeholder="Поиск по названию, научному названию или альтернативным названиям..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="species-search-input"
            />
            <Icon name="search" size="md" className="search-icon" />
          </div>

          <div className="species-list">
            {filteredSpecies.length === 0 ? (
              <div className="no-results">
                <Icon name="search_off" size="md" />
                <span>Виды рыб не найдены</span>
              </div>
            ) : (
              filteredSpecies.map(fish => (
                <div
                  key={fish.id}
                  className={`species-item ${isSelected(fish.id) ? 'selected' : ''} ${isMaxReached && !isSelected(fish.id) ? 'disabled' : ''}`}
                  onClick={() => handleToggleSpecies(fish)}
                >
                  <div className="species-checkbox">
                    {isSelected(fish.id) && <Icon name="check" size="sm" />}
                  </div>
                  <div className="species-info">
                    <div className="species-name">{fish.name}</div>
                    <div className="species-scientific">{fish.scientificName}</div>
                    {fish.alternativeNames.length > 0 && (
                      <div className="species-alternatives">
                        {fish.alternativeNames.slice(0, 3).join(', ')}
                        {fish.alternativeNames.length > 3 && '...'}
                      </div>
                    )}
                  </div>
                  <div className="species-category">
                    <span className={`category-badge ${fish.category}`}>
                      {fish.category === 'freshwater' ? 'Пресноводная' : 
                       fish.category === 'saltwater' ? 'Морская' : 'Солоноватоводная'}
                    </span>
                  </div>
                </div>
              ))
            )}
          </div>

          <div className="species-footer">
            <span className="selection-count">
              Выбрано: {selectedSpecies.length} из {maxSelections}
            </span>
            {isMaxReached && (
              <span className="max-reached">
                Достигнут лимит выбора
              </span>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default FishSpeciesSelector;





