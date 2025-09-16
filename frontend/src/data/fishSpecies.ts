export interface FishSpecies {
  id: string;
  name: string;
  scientificName: string;
  alternativeNames: string[];
  category: 'freshwater' | 'saltwater' | 'brackish';
  family: string;
  description?: string;
}

export const fishSpecies: FishSpecies[] = [
  // Пресноводные рыбы
  {
    id: 'pike',
    name: 'Щука',
    scientificName: 'Esox lucius',
    alternativeNames: ['northern pike', 'pike', 'щука обыкновенная', 'щука', 'pike-perch'],
    category: 'freshwater',
    family: 'Esocidae',
    description: 'Хищная рыба с удлиненным телом и острыми зубами'
  },
  {
    id: 'perch',
    name: 'Окунь',
    scientificName: 'Perca fluviatilis',
    alternativeNames: ['perch', 'european perch', 'окунь речной', 'окунь', 'perca'],
    category: 'freshwater',
    family: 'Percidae',
    description: 'Популярная пресноводная рыба с характерными полосами'
  },
  {
    id: 'bream',
    name: 'Лещ',
    scientificName: 'Abramis brama',
    alternativeNames: ['bream', 'common bream', 'лещ обыкновенный', 'лещ', 'brème'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Стайная рыба с высоким телом'
  },
  {
    id: 'carp',
    name: 'Карп',
    scientificName: 'Cyprinus carpio',
    alternativeNames: ['carp', 'common carp', 'карп обыкновенный', 'карп', 'carpe'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Крупная рыба, популярная в спортивной рыбалке'
  },
  {
    id: 'catfish',
    name: 'Сом',
    scientificName: 'Silurus glanis',
    alternativeNames: ['catfish', 'wels catfish', 'сом обыкновенный', 'сом', 'silure'],
    category: 'freshwater',
    family: 'Siluridae',
    description: 'Крупная хищная рыба без чешуи'
  },
  {
    id: 'pike-perch',
    name: 'Судак',
    scientificName: 'Sander lucioperca',
    alternativeNames: ['pike-perch', 'zander', 'судак обыкновенный', 'судак', 'sandre'],
    category: 'freshwater',
    family: 'Percidae',
    description: 'Хищная рыба с острыми зубами'
  },
  {
    id: 'roach',
    name: 'Плотва',
    scientificName: 'Rutilus rutilus',
    alternativeNames: ['roach', 'common roach', 'плотва обыкновенная', 'плотва', 'gardon'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Стайная рыба с серебристой чешуей'
  },
  {
    id: 'rudd',
    name: 'Красноперка',
    scientificName: 'Scardinius erythrophthalmus',
    alternativeNames: ['rudd', 'red eye', 'красноперка', 'красноперка обыкновенная', 'rotengle'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Рыба с красными плавниками и глазами'
  },
  {
    id: 'tench',
    name: 'Линь',
    scientificName: 'Tinca tinca',
    alternativeNames: ['tench', 'doctor fish', 'линь', 'линь обыкновенный', 'tanche'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Рыба с толстой слизистой кожей'
  },
  {
    id: 'crucian-carp',
    name: 'Карась',
    scientificName: 'Carassius carassius',
    alternativeNames: ['crucian carp', 'goldfish', 'карась', 'карась обыкновенный', 'carassin'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Выносливая рыба, обитающая в стоячих водах'
  },
  {
    id: 'ide',
    name: 'Язь',
    scientificName: 'Leuciscus idus',
    alternativeNames: ['ide', 'orfe', 'язь', 'язь обыкновенный', 'ide mélanote'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Рыба с золотистой чешуей'
  },
  {
    id: 'chub',
    name: 'Голавль',
    scientificName: 'Squalius cephalus',
    alternativeNames: ['chub', 'european chub', 'голавль', 'голавль обыкновенный', 'chevesne'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Рыба с большой головой и толстыми губами'
  },
  {
    id: 'asp',
    name: 'Жерех',
    scientificName: 'Aspius aspius',
    alternativeNames: ['asp', 'european asp', 'жерех', 'жерех обыкновенный', 'aspe'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Хищная рыба с вытянутым телом'
  },
  {
    id: 'barbel',
    name: 'Усач',
    scientificName: 'Barbus barbus',
    alternativeNames: ['barbel', 'european barbel', 'усач', 'усач обыкновенный', 'barbeau'],
    category: 'freshwater',
    family: 'Cyprinidae',
    description: 'Рыба с характерными усами'
  },
  {
    id: 'trout',
    name: 'Форель',
    scientificName: 'Salmo trutta',
    alternativeNames: ['trout', 'brown trout', 'форель', 'форель ручьевая', 'truite'],
    category: 'freshwater',
    family: 'Salmonidae',
    description: 'Рыба с пятнистой окраской'
  },
  {
    id: 'salmon',
    name: 'Лосось',
    scientificName: 'Salmo salar',
    alternativeNames: ['salmon', 'atlantic salmon', 'лосось', 'лосось атлантический', 'saumon'],
    category: 'freshwater',
    family: 'Salmonidae',
    description: 'Проходная рыба, нерестится в реках'
  },
  {
    id: 'whitefish',
    name: 'Сиг',
    scientificName: 'Coregonus lavaretus',
    alternativeNames: ['whitefish', 'common whitefish', 'сиг', 'сиг обыкновенный', 'corégone'],
    category: 'freshwater',
    family: 'Salmonidae',
    description: 'Рыба с серебристой чешуей'
  },
  {
    id: 'grayling',
    name: 'Хариус',
    scientificName: 'Thymallus thymallus',
    alternativeNames: ['grayling', 'european grayling', 'хариус', 'хариус европейский', 'ombre'],
    category: 'freshwater',
    family: 'Salmonidae',
    description: 'Рыба с большим спинным плавником'
  },
  {
    id: 'eel',
    name: 'Угорь',
    scientificName: 'Anguilla anguilla',
    alternativeNames: ['eel', 'european eel', 'угорь', 'угорь европейский', 'anguille'],
    category: 'freshwater',
    family: 'Anguillidae',
    description: 'Змеевидная рыба, мигрирует в океан'
  },
  {
    id: 'pike-perch',
    name: 'Берш',
    scientificName: 'Sander volgensis',
    alternativeNames: ['berch', 'volga pike-perch', 'берш', 'берш волжский', 'sandre de la Volga'],
    category: 'freshwater',
    family: 'Percidae',
    description: 'Рыба, похожая на судака, но меньше размером'
  },

  // Морские рыбы
  {
    id: 'cod',
    name: 'Треска',
    scientificName: 'Gadus morhua',
    alternativeNames: ['cod', 'atlantic cod', 'треска', 'треска атлантическая', 'morue'],
    category: 'saltwater',
    family: 'Gadidae',
    description: 'Популярная морская рыба'
  },
  {
    id: 'herring',
    name: 'Сельдь',
    scientificName: 'Clupea harengus',
    alternativeNames: ['herring', 'atlantic herring', 'сельдь', 'сельдь атлантическая', 'hareng'],
    category: 'saltwater',
    family: 'Clupeidae',
    description: 'Стайная морская рыба'
  },
  {
    id: 'mackerel',
    name: 'Скумбрия',
    scientificName: 'Scomber scombrus',
    alternativeNames: ['mackerel', 'atlantic mackerel', 'скумбрия', 'скумбрия атлантическая', 'maquereau'],
    category: 'saltwater',
    family: 'Scombridae',
    description: 'Быстрая морская рыба с полосами'
  },
  {
    id: 'tuna',
    name: 'Тунец',
    scientificName: 'Thunnus thynnus',
    alternativeNames: ['tuna', 'bluefin tuna', 'тунец', 'тунец голубой', 'thon'],
    category: 'saltwater',
    family: 'Scombridae',
    description: 'Крупная морская рыба'
  },
  {
    id: 'bass',
    name: 'Морской окунь',
    scientificName: 'Dicentrarchus labrax',
    alternativeNames: ['bass', 'european sea bass', 'морской окунь', 'лаврак', 'bar'],
    category: 'saltwater',
    family: 'Moronidae',
    description: 'Морская рыба с серебристой чешуей'
  },
  {
    id: 'flounder',
    name: 'Камбала',
    scientificName: 'Platichthys flesus',
    alternativeNames: ['flounder', 'european flounder', 'камбала', 'камбала европейская', 'flet'],
    category: 'saltwater',
    family: 'Pleuronectidae',
    description: 'Плоская рыба, лежит на боку'
  },
  {
    id: 'sole',
    name: 'Морской язык',
    scientificName: 'Solea solea',
    alternativeNames: ['sole', 'common sole', 'морской язык', 'солея', 'sole commune'],
    category: 'saltwater',
    family: 'Soleidae',
    description: 'Плоская рыба с нежным мясом'
  },
  {
    id: 'redfish',
    name: 'Красная рыба',
    scientificName: 'Sebastes marinus',
    alternativeNames: ['redfish', 'ocean perch', 'красная рыба', 'морской окунь красный', 'sébaste'],
    category: 'saltwater',
    family: 'Sebastidae',
    description: 'Морская рыба с красной окраской'
  },
  {
    id: 'halibut',
    name: 'Палтус',
    scientificName: 'Hippoglossus hippoglossus',
    alternativeNames: ['halibut', 'atlantic halibut', 'палтус', 'палтус атлантический', 'flétan'],
    category: 'saltwater',
    family: 'Pleuronectidae',
    description: 'Крупная плоская рыба'
  },
  {
    id: 'pollock',
    name: 'Минтай',
    scientificName: 'Pollachius pollachius',
    alternativeNames: ['pollock', 'european pollock', 'минтай', 'минтай европейский', 'lieu'],
    category: 'saltwater',
    family: 'Gadidae',
    description: 'Морская рыба семейства тресковых'
  },

  // Солоноватоводные рыбы
  {
    id: 'mullet',
    name: 'Кефаль',
    scientificName: 'Mugil cephalus',
    alternativeNames: ['mullet', 'flathead mullet', 'кефаль', 'кефаль обыкновенная', 'mulet'],
    category: 'brackish',
    family: 'Mugilidae',
    description: 'Рыба, обитающая в устьях рек'
  },
  {
    id: 'sea-bream',
    name: 'Морской лещ',
    scientificName: 'Sparus aurata',
    alternativeNames: ['sea bream', 'gilt-head bream', 'морской лещ', 'дорада', 'dorade'],
    category: 'brackish',
    family: 'Sparidae',
    description: 'Рыба с золотистым пятном на голове'
  },
  {
    id: 'striped-bass',
    name: 'Полосатый окунь',
    scientificName: 'Morone saxatilis',
    alternativeNames: ['striped bass', 'striper', 'полосатый окунь', 'бас полосатый', 'bar rayé'],
    category: 'brackish',
    family: 'Moronidae',
    description: 'Рыба с характерными полосами'
  }
];

// Функция поиска видов рыб
export const searchFishSpecies = (query: string): FishSpecies[] => {
  if (!query.trim()) return fishSpecies;
  
  const searchTerm = query.toLowerCase().trim();
  
  return fishSpecies.filter(fish => 
    fish.name.toLowerCase().includes(searchTerm) ||
    fish.scientificName.toLowerCase().includes(searchTerm) ||
    fish.alternativeNames.some(alt => alt.toLowerCase().includes(searchTerm)) ||
    fish.family.toLowerCase().includes(searchTerm)
  );
};

// Функция получения видов рыб по категории
export const getFishSpeciesByCategory = (category: 'freshwater' | 'saltwater' | 'brackish' | 'all'): FishSpecies[] => {
  if (category === 'all') return fishSpecies;
  return fishSpecies.filter(fish => fish.category === category);
};

// Функция получения популярных видов рыб
export const getPopularFishSpecies = (): FishSpecies[] => {
  const popularIds = ['pike', 'perch', 'bream', 'carp', 'catfish', 'pike-perch', 'roach', 'cod', 'herring', 'mackerel'];
  return fishSpecies.filter(fish => popularIds.includes(fish.id));
};





