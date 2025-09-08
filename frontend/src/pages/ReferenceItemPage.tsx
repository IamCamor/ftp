import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import SEOHead from '../components/SEOHead';
import PageHeader from '../components/PageHeader';
import Icon from '../components/Icon';
import { request } from '../utils/http';

interface ReferenceItem {
  id: number;
  name: string;
  slug: string;
  description?: string;
  photo_url?: string;
  additional_photos?: string[];
  view_count: number;
  [key: string]: any;
}

const ReferenceItemPage: React.FC = () => {
  const { type, slug } = useParams<{ type: string; slug: string }>();
  const navigate = useNavigate();
  const [item, setItem] = useState<ReferenceItem | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (type && slug) {
      loadItem(type, slug);
    }
  }, [type, slug]);

  const loadItem = async (itemType: string, itemSlug: string) => {
    setLoading(true);
    setError(null);
    
    try {
      const endpoint = getEndpointForType(itemType, itemSlug);
      const response = await request(endpoint);
      setItem(response.data);
    } catch (error) {
      console.error('Error loading item:', error);
      setError('Элемент справочника не найден');
    } finally {
      setLoading(false);
    }
  };

  const getEndpointForType = (itemType: string, itemSlug: string): string => {
    switch (itemType) {
      case 'fish_species':
        return `/fish-species/${itemSlug}`;
      case 'fishing_knots':
        return `/fishing-knots/${itemSlug}`;
      case 'boats':
        return `/boats/${itemSlug}`;
      case 'fishing_methods':
        return `/fishing-methods/${itemSlug}`;
      case 'fishing_tackle':
        return `/fishing-tackle/${itemSlug}`;
      case 'boat_engines':
        return `/boat-engines/${itemSlug}`;
      case 'fishing_locations':
        return `/fishing-locations/${itemSlug}`;
      default:
        throw new Error('Unknown reference type');
    }
  };

  const getTypeTitle = (type: string): string => {
    const typeMap: Record<string, string> = {
      'fish_species': 'Вид рыбы',
      'fishing_knots': 'Рыболовный узел',
      'boats': 'Лодка',
      'fishing_methods': 'Способ ловли',
      'fishing_tackle': 'Снасть',
      'boat_engines': 'Мотор',
      'fishing_locations': 'Место ловли',
    };
    return typeMap[type] || 'Справочник';
  };

  const renderFishSpeciesContent = (item: ReferenceItem) => (
    <div className="reference-item-content">
      {item.scientific_name && (
        <div className="info-section">
          <h3>Научное название</h3>
          <p className="scientific-name">{item.scientific_name}</p>
        </div>
      )}
      
      {item.habitat && (
        <div className="info-section">
          <h3>Места обитания</h3>
          <p>{item.habitat}</p>
        </div>
      )}
      
      {item.feeding_habits && (
        <div className="info-section">
          <h3>Особенности питания</h3>
          <p>{item.feeding_habits}</p>
        </div>
      )}
      
      {(item.min_size || item.max_size) && (
        <div className="info-section">
          <h3>Размер</h3>
          <p>{item.size_range}</p>
        </div>
      )}
      
      {(item.min_weight || item.max_weight) && (
        <div className="info-section">
          <h3>Вес</h3>
          <p>{item.weight_range}</p>
        </div>
      )}
      
      {item.category && (
        <div className="info-section">
          <h3>Категория</h3>
          <p>{item.category_display_name}</p>
        </div>
      )}
      
      {item.is_protected && (
        <div className="info-section warning">
          <h3>⚠️ Красная книга</h3>
          <p>Данный вид рыбы находится под защитой</p>
        </div>
      )}
    </div>
  );

  const renderFishingKnotContent = (item: ReferenceItem) => (
    <div className="reference-item-content">
      {item.purpose && (
        <div className="info-section">
          <h3>Назначение</h3>
          <p>{item.purpose}</p>
        </div>
      )}
      
      {item.difficulty && (
        <div className="info-section">
          <h3>Сложность</h3>
          <p>{item.difficulty_display_name}</p>
        </div>
      )}
      
      {item.strength_percentage && (
        <div className="info-section">
          <h3>Прочность</h3>
          <p>{item.strength_display}</p>
        </div>
      )}
      
      {item.instructions && (
        <div className="info-section">
          <h3>Инструкция</h3>
          <div className="instructions">
            {item.instructions.split('\n').map((line: string, index: number) => (
              <p key={index}>{line}</p>
            ))}
          </div>
        </div>
      )}
      
      {item.use_cases && item.use_cases.length > 0 && (
        <div className="info-section">
          <h3>Применение</h3>
          <ul>
            {item.use_cases.map((useCase: string, index: number) => (
              <li key={index}>{useCase}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );

  const renderBoatContent = (item: ReferenceItem) => (
    <div className="reference-item-content">
      {item.brand && (
        <div className="info-section">
          <h3>Бренд</h3>
          <p>{item.brand}</p>
        </div>
      )}
      
      {item.type && (
        <div className="info-section">
          <h3>Тип</h3>
          <p>{item.type_display_name}</p>
        </div>
      )}
      
      {(item.length || item.width) && (
        <div className="info-section">
          <h3>Размеры</h3>
          <p>{item.dimensions}</p>
        </div>
      )}
      
      {item.capacity && (
        <div className="info-section">
          <h3>Вместимость</h3>
          <p>{item.capacity} человек</p>
        </div>
      )}
      
      {item.material && (
        <div className="info-section">
          <h3>Материал</h3>
          <p>{item.material}</p>
        </div>
      )}
      
      {(item.price_min || item.price_max) && (
        <div className="info-section">
          <h3>Цена</h3>
          <p>{item.price_range}</p>
        </div>
      )}
      
      {item.pros && item.pros.length > 0 && (
        <div className="info-section">
          <h3>Преимущества</h3>
          <ul className="pros-list">
            {item.pros.map((pro: string, index: number) => (
              <li key={index} className="pro-item">✓ {pro}</li>
            ))}
          </ul>
        </div>
      )}
      
      {item.cons && item.cons.length > 0 && (
        <div className="info-section">
          <h3>Недостатки</h3>
          <ul className="cons-list">
            {item.cons.map((con: string, index: number) => (
              <li key={index} className="con-item">✗ {con}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );

  const renderContent = () => {
    if (!item) return null;

    switch (type) {
      case 'fish_species':
        return renderFishSpeciesContent(item);
      case 'fishing_knots':
        return renderFishingKnotContent(item);
      case 'boats':
        return renderBoatContent(item);
      default:
        return (
          <div className="reference-item-content">
            {item.description && (
              <div className="info-section">
                <p>{item.description}</p>
              </div>
            )}
          </div>
        );
    }
  };

  if (loading) {
    return (
      <div className="page">
        <PageHeader title="Загрузка..." />
        <div className="loading">Загрузка...</div>
      </div>
    );
  }

  if (error || !item) {
    return (
      <div className="page">
        <PageHeader title="Ошибка" />
        <div className="error">
          <Icon name="error" />
          <h3>{error || 'Элемент не найден'}</h3>
          <button 
            onClick={() => navigate('/reference')}
            className="btn btn-primary"
          >
            Вернуться к справочникам
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="page">
      <SEOHead
        title={item.name}
        description={item.description || `${getTypeTitle(type!)} ${item.name} - подробная информация в справочнике FishTrackPro`}
        keywords={[item.name, getTypeTitle(type!), 'справочник', 'рыбалка']}
        type="article"
        image={item.photo_url}
      />
      
      <PageHeader 
        title={item.name}
        showBack
        onBack={() => navigate(`/reference/${type}`)}
      />
      
      <div className="reference-item-page">
        <div className="reference-item-header">
          <div className="reference-item-image">
            {item.photo_url ? (
              <img 
                src={item.photo_url} 
                alt={item.name}
                className="main-photo"
              />
            ) : (
              <div className="no-photo">
                <Icon name="image" />
              </div>
            )}
          </div>
          
          <div className="reference-item-info">
            <div className="item-type">
              <Icon name="info" />
              <span>{getTypeTitle(type!)}</span>
            </div>
            
            <div className="item-stats">
              <div className="stat">
                <Icon name="visibility" />
                <span>{item.view_count} просмотров</span>
              </div>
            </div>
          </div>
        </div>

        {renderContent()}

        {item.additional_photos && item.additional_photos.length > 0 && (
          <div className="additional-photos">
            <h3>Дополнительные фото</h3>
            <div className="photos-grid">
              {item.additional_photos.map((photo: string, index: number) => (
                <img 
                  key={index}
                  src={photo} 
                  alt={`${item.name} - фото ${index + 1}`}
                  className="additional-photo"
                />
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ReferenceItemPage;
