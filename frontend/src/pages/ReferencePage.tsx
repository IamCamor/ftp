import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import SEOHead from '../components/SEOHead';
import PageHeader from '../components/PageHeader';
import ReferenceCard from '../components/ReferenceCard';
import Icon from '../components/Icon';
import { request } from '../utils/http';

interface ReferenceType {
  name: string;
  count: number;
  icon: string;
  description: string;
}

interface ReferenceItem {
  id: number;
  name: string;
  slug: string;
  description?: string;
  photo_url?: string;
  view_count: number;
  [key: string]: any;
}

const ReferencePage: React.FC = () => {
  const { type } = useParams<{ type: string }>();
  const navigate = useNavigate();
  const [referenceTypes, setReferenceTypes] = useState<ReferenceType[]>([]);
  const [items, setItems] = useState<ReferenceItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedType, setSelectedType] = useState<string | null>(type || null);

  useEffect(() => {
    loadReferenceTypes();
    if (selectedType) {
      loadItems(selectedType);
    }
  }, [selectedType]);

  const loadReferenceTypes = async () => {
    try {
      const response = await request('/references');
      setReferenceTypes(response.data);
    } catch (error) {
      console.error('Error loading reference types:', error);
    }
  };

  const loadItems = async (referenceType: string) => {
    setLoading(true);
    try {
      const endpoint = getEndpointForType(referenceType);
      const response = await request(endpoint);
      setItems(response.data.data || response.data);
    } catch (error) {
      console.error('Error loading items:', error);
    } finally {
      setLoading(false);
    }
  };

  const getEndpointForType = (referenceType: string): string => {
    switch (referenceType) {
      case 'fish_species':
        return '/fish-species';
      case 'fishing_knots':
        return '/fishing-knots';
      case 'boats':
        return '/boats';
      case 'fishing_methods':
        return '/fishing-methods';
      case 'fishing_tackle':
        return '/fishing-tackle';
      case 'boat_engines':
        return '/boat-engines';
      case 'fishing_locations':
        return '/fishing-locations';
      default:
        return '/references';
    }
  };

  const handleTypeSelect = (referenceType: string) => {
    setSelectedType(referenceType);
    navigate(`/reference/${referenceType}`);
  };

  const handleSearch = async () => {
    if (!searchQuery.trim()) {
      if (selectedType) {
        loadItems(selectedType);
      }
      return;
    }

    setLoading(true);
    try {
      const response = await request('/references/search', {
        params: { query: searchQuery, type: selectedType }
      });
      
      if (selectedType && response.data[selectedType]) {
        setItems(response.data[selectedType]);
      } else {
        // If no specific type, combine all results
        const allItems: ReferenceItem[] = [];
        Object.values(response.data).forEach((typeItems: any) => {
          allItems.push(...typeItems);
        });
        setItems(allItems);
      }
    } catch (error) {
      console.error('Error searching:', error);
    } finally {
      setLoading(false);
    }
  };

  const getTypeTitle = (type: string): string => {
    const typeMap: Record<string, string> = {
      'fish_species': 'Виды рыб',
      'fishing_knots': 'Рыболовные узлы',
      'boats': 'Лодки',
      'fishing_methods': 'Способы ловли',
      'fishing_tackle': 'Снасти',
      'boat_engines': 'Моторы',
      'fishing_locations': 'Места ловли',
    };
    return typeMap[type] || 'Справочник';
  };

  const getTypeDescription = (type: string): string => {
    const typeMap: Record<string, string> = {
      'fish_species': 'Полный справочник видов рыб с описаниями, характеристиками и местами обитания',
      'fishing_knots': 'Коллекция рыболовных узлов с пошаговыми инструкциями и советами',
      'boats': 'Каталог лодок для рыбалки с техническими характеристиками и отзывами',
      'fishing_methods': 'Методы и техники рыбной ловли с подробными описаниями',
      'fishing_tackle': 'Рыболовные снасти и оборудование с характеристиками и ценами',
      'boat_engines': 'Лодочные моторы и двигатели с техническими данными',
      'fishing_locations': 'Популярные места для рыбалки с описаниями и советами',
    };
    return typeMap[type] || 'Справочная информация для рыбаков';
  };

  return (
    <div className="page">
      <SEOHead
        title={selectedType ? getTypeTitle(selectedType) : 'Справочники'}
        description={selectedType ? getTypeDescription(selectedType) : 'Полные справочники для рыбаков: виды рыб, узлы, лодки, способы ловли, снасти, моторы и места'}
        keywords={['справочники', 'рыбалка', 'виды рыб', 'узлы', 'лодки', 'снасти', 'способы ловли']}
        type="website"
      />
      
      <PageHeader title={selectedType ? getTypeTitle(selectedType) : 'Справочники'} />
      
      <div className="reference-page">
        {/* Search */}
        <div className="reference-search">
          <div className="search-input-group">
            <input
              type="text"
              placeholder="Поиск в справочниках..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
              className="search-input"
            />
            <button 
              onClick={handleSearch}
              className="search-button"
            >
              <Icon name="search" />
            </button>
          </div>
        </div>

        {/* Reference Types */}
        {!selectedType && (
          <div className="reference-types">
            <h2>Выберите категорию</h2>
            <div className="reference-types-grid">
              {referenceTypes.map((refType) => (
                <div 
                  key={refType.name}
                  className="reference-type-card"
                  onClick={() => handleTypeSelect(refType.name)}
                >
                  <div className="reference-type-icon">
                    <Icon name={refType.icon} />
                  </div>
                  <h3>{refType.name}</h3>
                  <p>{refType.description}</p>
                  <div className="reference-type-count">
                    {refType.count} записей
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Items List */}
        {selectedType && (
          <div className="reference-items">
            <div className="reference-header">
              <h2>{getTypeTitle(selectedType)}</h2>
              <p>{getTypeDescription(selectedType)}</p>
            </div>

            {loading ? (
              <div className="loading">Загрузка...</div>
            ) : items.length > 0 ? (
              <div className="reference-items-grid">
                {items.map((item) => (
                  <ReferenceCard
                    key={item.id}
                    id={item.id}
                    name={item.name}
                    description={item.description}
                    photoUrl={item.photo_url}
                    type={selectedType as any}
                    slug={item.slug}
                    viewCount={item.view_count}
                    additionalInfo={{
                      category: item.category_display_name || item.category,
                      difficulty: item.difficulty_display_name || item.difficulty,
                      price_range: item.price_range,
                    }}
                  />
                ))}
              </div>
            ) : (
              <div className="empty-state">
                <Icon name="search_off" />
                <h3>Ничего не найдено</h3>
                <p>Попробуйте изменить поисковый запрос</p>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default ReferencePage;
