import React from 'react';
import Icon from './Icon';

interface MapFiltersProps {
  fishSpecies: string[];
  selectedSpecies: string[];
  pointTypes: string[];
  selectedPointTypes: string[];
  onSpeciesChange: (species: string[]) => void;
  onPointTypesChange: (types: string[]) => void;
  onClearFilters: () => void;
  onClose: () => void;
}

const MapFilters: React.FC<MapFiltersProps> = ({
  fishSpecies,
  selectedSpecies,
  pointTypes,
  selectedPointTypes,
  onSpeciesChange,
  onPointTypesChange,
  onClearFilters,
  onClose
}) => {
  const handleSpeciesToggle = (species: string) => {
    if (selectedSpecies.includes(species)) {
      onSpeciesChange(selectedSpecies.filter(s => s !== species));
    } else {
      onSpeciesChange([...selectedSpecies, species]);
    }
  };

  const handlePointTypeToggle = (type: string) => {
    if (selectedPointTypes.includes(type)) {
      onPointTypesChange(selectedPointTypes.filter(t => t !== type));
    } else {
      onPointTypesChange([...selectedPointTypes, type]);
    }
  };


  const getPointTypeIcon = (type: string) => {
    switch (type.toLowerCase()) {
      case 'улов':
        return 'pets';
      case 'место':
        return 'place';
      case 'погода':
        return 'wb_sunny';
      default:
        return 'place';
    }
  };

  return (
    <div className="glass-card p-6 mx-4 mt-4 max-w-sm">
      <div className="flex items-center justify-between mb-6">
        <h3 className="md3-title-large">Фильтры</h3>
        <div className="flex items-center space-x-2">
          <button 
            className="md3-button md3-button-text p-2"
            onClick={onClearFilters}
            title="Очистить все фильтры"
          >
            <Icon name="clear" size={20} />
          </button>
          <button 
            className="md3-button md3-button-text p-2"
            onClick={onClose}
            title="Закрыть фильтры"
          >
            <Icon name="close" size={20} />
          </button>
        </div>
      </div>

      <div className="space-y-6">
        <div>
          <h4 className="md3-title-medium mb-3">Виды рыб</h4>
          <div className="flex flex-wrap gap-2">
            {fishSpecies.map(species => (
              <button
                key={species}
                className={`md3-chip ${selectedSpecies.includes(species) ? 'selected' : ''}`}
                onClick={() => handleSpeciesToggle(species)}
              >
                <Icon name="pets" size={16} className="mr-1" />
                <span>{species}</span>
              </button>
            ))}
          </div>
        </div>

        <div>
          <h4 className="md3-title-medium mb-3">Тип места</h4>
          <div className="flex flex-wrap gap-2">
            {pointTypes.map(type => (
              <button
                key={type}
                className={`md3-chip ${selectedPointTypes.includes(type) ? 'selected' : ''}`}
                onClick={() => handlePointTypeToggle(type)}
              >
                <Icon name={getPointTypeIcon(type)} size={16} className="mr-1" />
                <span>{type}</span>
              </button>
            ))}
          </div>
        </div>

        <div className="pt-4 border-t border-gray-200">
          <p className="md3-body-small text-gray-600">
            Показано: {selectedSpecies.length > 0 ? selectedSpecies.length : fishSpecies.length} видов рыб, 
            {selectedPointTypes.length > 0 ? selectedPointTypes.length : pointTypes.length} типов мест
          </p>
        </div>
      </div>
    </div>
  );
};

export default MapFilters;
