import type { FishSpecies, FishingMethod, Bait, Location, ReferenceSearchResult } from '../types';

// Локальные данные справочников
export const fishSpecies: FishSpecies[] = [
  {
    id: 1,
    name: 'Щука',
    scientific_name: 'Esox lucius',
    description: 'Хищная рыба семейства щуковых. Одна из самых популярных рыб для спортивной рыбалки.',
    image_url: '/images/fish/pike.jpg',
    habitat: 'Озера, реки, пруды',
    size_range: '30-120 см',
    weight_range: '0.5-20 кг',
    season: 'Круглый год, лучше весна и осень',
    bait: ['Живец', 'Блесна', 'Воблер', 'Джиг'],
    fishing_methods: ['Спиннинг', 'Жерлицы', 'Донка'],
    regulations: 'Минимальный размер 35 см',
    conservation_status: 'Обычный вид',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    name: 'Окунь',
    scientific_name: 'Perca fluviatilis',
    description: 'Пресноводная рыба семейства окуневых. Очень активная и агрессивная рыба.',
    image_url: '/images/fish/perch.jpg',
    habitat: 'Озера, реки, пруды',
    size_range: '15-50 см',
    weight_range: '0.1-3 кг',
    season: 'Круглый год',
    bait: ['Червь', 'Мотыль', 'Блесна', 'Воблер'],
    fishing_methods: ['Поплавочная удочка', 'Спиннинг', 'Донка'],
    regulations: 'Минимальный размер 15 см',
    conservation_status: 'Обычный вид',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 3,
    name: 'Карп',
    scientific_name: 'Cyprinus carpio',
    description: 'Крупная пресноводная рыба семейства карповых. Популярная рыба для спортивной рыбалки.',
    image_url: '/images/fish/carp.jpg',
    habitat: 'Озера, пруды, реки',
    size_range: '30-100 см',
    weight_range: '1-30 кг',
    season: 'Весна-осень, лучше лето',
    bait: ['Кукуруза', 'Бойлы', 'Червь', 'Хлеб'],
    fishing_methods: ['Донка', 'Поплавочная удочка', 'Фидер'],
    regulations: 'Минимальный размер 30 см',
    conservation_status: 'Обычный вид',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 4,
    name: 'Судак',
    scientific_name: 'Sander lucioperca',
    description: 'Хищная рыба семейства окуневых. Ценная промысловая рыба.',
    image_url: '/images/fish/pike-perch.jpg',
    habitat: 'Реки, озера, водохранилища',
    size_range: '40-130 см',
    weight_range: '1-15 кг',
    season: 'Круглый год, лучше осень',
    bait: ['Живец', 'Блесна', 'Воблер', 'Джиг'],
    fishing_methods: ['Спиннинг', 'Донка', 'Жерлицы'],
    regulations: 'Минимальный размер 40 см',
    conservation_status: 'Обычный вид',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 5,
    name: 'Лещ',
    scientific_name: 'Abramis brama',
    description: 'Пресноводная рыба семейства карповых. Популярная рыба для поплавочной ловли.',
    image_url: '/images/fish/bream.jpg',
    habitat: 'Реки, озера, водохранилища',
    size_range: '20-70 см',
    weight_range: '0.3-6 кг',
    season: 'Весна-осень',
    bait: ['Червь', 'Мотыль', 'Опарыш', 'Тесто'],
    fishing_methods: ['Поплавочная удочка', 'Фидер', 'Донка'],
    regulations: 'Минимальный размер 25 см',
    conservation_status: 'Обычный вид',
    created_at: '2024-01-01T00:00:00Z'
  }
];

export const fishingMethods: FishingMethod[] = [
  {
    id: 1,
    name: 'Спиннинг',
    description: 'Активный метод ловли хищных рыб с использованием искусственных приманок.',
    image_url: '/images/methods/spinning.jpg',
    equipment: ['Спиннинг', 'Катушка', 'Леска', 'Приманки'],
    techniques: ['Твичинг', 'Джиг', 'Троллинг', 'Кастинг'],
    best_season: 'Весна-осень',
    difficulty_level: 'intermediate',
    tips: [
      'Выбирайте приманку под условия ловли',
      'Экспериментируйте с проводкой',
      'Обращайте внимание на погоду'
    ],
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    name: 'Поплавочная удочка',
    description: 'Классический метод ловли мирных рыб с поплавком.',
    image_url: '/images/methods/float-fishing.jpg',
    equipment: ['Удочка', 'Леска', 'Поплавок', 'Грузила', 'Крючки'],
    techniques: ['Ловля в проводку', 'Стационарная ловля', 'Ловля на течении'],
    best_season: 'Весна-осень',
    difficulty_level: 'beginner',
    tips: [
      'Настройте поплавок правильно',
      'Используйте прикормку',
      'Выбирайте правильную глубину'
    ],
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 3,
    name: 'Фидер',
    description: 'Донный метод ловли с использованием кормушки.',
    image_url: '/images/methods/feeder.jpg',
    equipment: ['Фидерное удилище', 'Катушка', 'Кормушка', 'Леска'],
    techniques: ['Ловля на течении', 'Ловля в стоячей воде', 'Дальний заброс'],
    best_season: 'Круглый год',
    difficulty_level: 'intermediate',
    tips: [
      'Правильно настройте снасть',
      'Используйте качественную прикормку',
      'Экспериментируйте с дистанцией'
    ],
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 4,
    name: 'Донка',
    description: 'Донный метод ловли с использованием грузила.',
    image_url: '/images/methods/bottom-fishing.jpg',
    equipment: ['Удилище', 'Катушка', 'Грузило', 'Леска', 'Крючки'],
    techniques: ['Ловля на течении', 'Ловля в стоячей воде', 'Ловля на живца'],
    best_season: 'Круглый год',
    difficulty_level: 'beginner',
    tips: [
      'Выбирайте правильное грузило',
      'Используйте качественную наживку',
      'Обращайте внимание на поклевку'
    ],
    created_at: '2024-01-01T00:00:00Z'
  }
];

export const baits: Bait[] = [
  {
    id: 1,
    name: 'Червь',
    type: 'natural',
    description: 'Универсальная наживка для ловли различных видов рыб.',
    image_url: '/images/baits/worm.jpg',
    target_species: ['Лещ', 'Окунь', 'Плотва', 'Карась'],
    best_season: 'Весна-осень',
    preparation: 'Насаживать на крючок целиком или частями',
    storage_tips: 'Хранить в прохладном месте с влажной землей',
    effectiveness_rating: 9,
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    name: 'Блесна',
    type: 'artificial',
    description: 'Искусственная приманка для ловли хищных рыб.',
    image_url: '/images/baits/spinner.jpg',
    target_species: ['Щука', 'Окунь', 'Судак', 'Жерех'],
    best_season: 'Весна-осень',
    preparation: 'Привязать к леске через вертлюжок',
    storage_tips: 'Хранить в сухом месте, смазывать маслом',
    effectiveness_rating: 8,
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 3,
    name: 'Кукуруза',
    type: 'natural',
    description: 'Растительная наживка для ловли карповых рыб.',
    image_url: '/images/baits/corn.jpg',
    target_species: ['Карп', 'Лещ', 'Карась', 'Плотва'],
    best_season: 'Лето-осень',
    preparation: 'Использовать консервированную или вареную',
    storage_tips: 'Хранить в холодильнике',
    effectiveness_rating: 7,
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 4,
    name: 'Воблер',
    type: 'artificial',
    description: 'Искусственная приманка, имитирующая рыбку.',
    image_url: '/images/baits/crankbait.jpg',
    target_species: ['Щука', 'Окунь', 'Судак', 'Форель'],
    best_season: 'Весна-осень',
    preparation: 'Привязать к леске через вертлюжок',
    storage_tips: 'Хранить в специальных коробках',
    effectiveness_rating: 9,
    created_at: '2024-01-01T00:00:00Z'
  }
];

export const locations: Location[] = [
  {
    id: 1,
    name: 'Озеро Байкал',
    type: 'lake',
    description: 'Самое глубокое озеро в мире, богатое рыбой.',
    image_url: '/images/locations/baikal.jpg',
    coordinates: { lat: 53.5, lng: 108.0 },
    region: 'Сибирь',
    country: 'Россия',
    fish_species: ['Омуль', 'Сиг', 'Хариус', 'Щука', 'Окунь'],
    facilities: ['Рыбацкие базы', 'Лодки', 'Снаряжение'],
    regulations: 'Лицензия обязательна',
    best_season: 'Лето-осень',
    access_info: 'Доступ на автомобиле или поезде',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    name: 'Река Волга',
    type: 'river',
    description: 'Крупнейшая река Европы, богатая рыбой.',
    image_url: '/images/locations/volga.jpg',
    coordinates: { lat: 56.0, lng: 38.0 },
    region: 'Центральная Россия',
    country: 'Россия',
    fish_species: ['Лещ', 'Судак', 'Щука', 'Окунь', 'Плотва'],
    facilities: ['Рыбацкие базы', 'Лодки', 'Причалы'],
    regulations: 'Соблюдать правила рыболовства',
    best_season: 'Весна-осень',
    access_info: 'Множество точек доступа',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 3,
    name: 'Рыбинское водохранилище',
    type: 'reservoir',
    description: 'Крупное водохранилище на Волге, популярное у рыболовов.',
    image_url: '/images/locations/rybinsk.jpg',
    coordinates: { lat: 58.0, lng: 38.5 },
    region: 'Ярославская область',
    country: 'Россия',
    fish_species: ['Лещ', 'Судак', 'Щука', 'Окунь', 'Плотва'],
    facilities: ['Рыбацкие базы', 'Лодки', 'Домики'],
    regulations: 'Лицензия на ловлю',
    best_season: 'Круглый год',
    access_info: 'Доступ на автомобиле',
    created_at: '2024-01-01T00:00:00Z'
  }
];

// Функция поиска по справочникам
export function searchReferences(query: string, type?: string): ReferenceSearchResult[] {
  const results: ReferenceSearchResult[] = [];
  const searchQuery = query.toLowerCase().trim();
  
  if (!searchQuery) return results;

  // Поиск по видам рыб
  if (!type || type === 'fish_species') {
    fishSpecies.forEach(fish => {
      if (
        fish.name.toLowerCase().includes(searchQuery) ||
        fish.scientific_name?.toLowerCase().includes(searchQuery) ||
        fish.description?.toLowerCase().includes(searchQuery) ||
        fish.habitat?.toLowerCase().includes(searchQuery)
      ) {
        results.push({
          type: 'fish_species',
          id: fish.id,
          title: fish.name,
          description: fish.description,
          image: fish.image_url,
          category: 'Вид рыбы',
          created_at: fish.created_at,
          data: fish
        });
      }
    });
  }

  // Поиск по методам ловли
  if (!type || type === 'fishing_method') {
    fishingMethods.forEach(method => {
      if (
        method.name.toLowerCase().includes(searchQuery) ||
        method.description?.toLowerCase().includes(searchQuery) ||
        method.techniques?.some(tech => tech.toLowerCase().includes(searchQuery))
      ) {
        results.push({
          type: 'fishing_method',
          id: method.id,
          title: method.name,
          description: method.description,
          image: method.image_url,
          category: 'Метод ловли',
          created_at: method.created_at,
          data: method
        });
      }
    });
  }

  // Поиск по наживкам
  if (!type || type === 'bait') {
    baits.forEach(bait => {
      if (
        bait.name.toLowerCase().includes(searchQuery) ||
        bait.description?.toLowerCase().includes(searchQuery) ||
        bait.target_species?.some(species => species.toLowerCase().includes(searchQuery))
      ) {
        results.push({
          type: 'bait',
          id: bait.id,
          title: bait.name,
          description: bait.description,
          image: bait.image_url,
          category: 'Наживка',
          created_at: bait.created_at,
          data: bait
        });
      }
    });
  }

  // Поиск по местам
  if (!type || type === 'location') {
    locations.forEach(location => {
      if (
        location.name.toLowerCase().includes(searchQuery) ||
        location.description?.toLowerCase().includes(searchQuery) ||
        location.region?.toLowerCase().includes(searchQuery) ||
        location.fish_species?.some(species => species.toLowerCase().includes(searchQuery))
      ) {
        results.push({
          type: 'location',
          id: location.id,
          title: location.name,
          description: location.description,
          image: location.image_url,
          category: 'Место ловли',
          created_at: location.created_at,
          data: location
        });
      }
    });
  }

  return results;
}

// Функция получения предложений для автодополнения
export function getReferenceSuggestions(query: string, type?: string): string[] {
  const suggestions: string[] = [];
  const searchQuery = query.toLowerCase().trim();
  
  if (!searchQuery) return suggestions;

  // Предложения по видам рыб
  if (!type || type === 'fish_species') {
    fishSpecies.forEach(fish => {
      if (fish.name.toLowerCase().includes(searchQuery)) {
        suggestions.push(fish.name);
      }
    });
  }

  // Предложения по методам ловли
  if (!type || type === 'fishing_method') {
    fishingMethods.forEach(method => {
      if (method.name.toLowerCase().includes(searchQuery)) {
        suggestions.push(method.name);
      }
    });
  }

  // Предложения по наживкам
  if (!type || type === 'bait') {
    baits.forEach(bait => {
      if (bait.name.toLowerCase().includes(searchQuery)) {
        suggestions.push(bait.name);
      }
    });
  }

  // Предложения по местам
  if (!type || type === 'location') {
    locations.forEach(location => {
      if (location.name.toLowerCase().includes(searchQuery)) {
        suggestions.push(location.name);
      }
    });
  }

  return [...new Set(suggestions)].slice(0, 10); // Убираем дубликаты и ограничиваем количество
}

