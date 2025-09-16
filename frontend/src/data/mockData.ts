// Тестовые данные для приложения

// Генерируем 10 мероприятий
const generateEvents = () => {
  const eventData = [
    {
      title: "Весенний турнир по спиннингу",
      description: "Ежегодный турнир по ловле хищной рыбы на спиннинг. Призы для победителей!",
      location: "Озеро Светлое",
      coordinates: { lat: 55.7558, lng: 37.6176 },
      maxParticipants: 50,
      currentParticipants: 23,
      organizer: {
        id: 1,
        name: "Рыболовный клуб 'Спиннинг'",
        avatar: "https://i.pravatar.cc/100?img=4"
      },
      tags: ["спиннинг", "турнир", "хищная рыба"],
      price: 1000,
      currency: "RUB"
    },
    {
      title: "Мастер-класс по вязанию узлов",
      description: "Изучаем основные рыболовные узлы и их применение",
      location: "Рыболовный магазин 'Удача'",
      coordinates: { lat: 55.7600, lng: 37.6200 },
      maxParticipants: 20,
      currentParticipants: 15,
      organizer: {
        id: 2,
        name: "Алексей Петров",
        avatar: "https://i.pravatar.cc/100?img=5"
      },
      tags: ["обучение", "узлы", "снасти"],
      price: 0,
      currency: "RUB"
    },
    {
      title: "Ночная рыбалка на сома",
      description: "Специальная ночная рыбалка на сома с использованием донных снастей",
      location: "Река Москва, участок у Крымского моста",
      coordinates: { lat: 55.7400, lng: 37.6000 },
      maxParticipants: 15,
      currentParticipants: 8,
      organizer: {
        id: 3,
        name: "Сомовед",
        avatar: "https://i.pravatar.cc/100?img=6"
      },
      tags: ["сом", "ночная рыбалка", "донка"],
      price: 500,
      currency: "RUB"
    },
    {
      title: "Карповый турнир 'Золотая рыбка'",
      description: "Турнир по ловле карпа на пруду. Призовой фонд 50,000 рублей!",
      location: "Пруд 'Золотая рыбка'",
      coordinates: { lat: 55.7500, lng: 37.6100 },
      maxParticipants: 30,
      currentParticipants: 18,
      organizer: {
        id: 4,
        name: "Карпятники России",
        avatar: "https://i.pravatar.cc/100?img=7"
      },
      tags: ["карп", "турнир", "бойлы"],
      price: 2000,
      currency: "RUB"
    },
    {
      title: "Зимняя рыбалка на мормышку",
      description: "Обучение технике ловли на мормышку в зимний период",
      location: "Озеро Селигер",
      coordinates: { lat: 57.2000, lng: 33.1000 },
      maxParticipants: 25,
      currentParticipants: 12,
      organizer: {
        id: 5,
        name: "Зимняя рыбалка",
        avatar: "https://i.pravatar.cc/100?img=8"
      },
      tags: ["зимняя рыбалка", "мормышка", "обучение"],
      price: 800,
      currency: "RUB"
    },
    {
      title: "Морская рыбалка в Крыму",
      description: "Рыбалка в Черном море с лодки. Ловля кефали и ставриды",
      location: "Севастополь, Черное море",
      coordinates: { lat: 44.6000, lng: 33.5000 },
      maxParticipants: 12,
      currentParticipants: 6,
      organizer: {
        id: 6,
        name: "Морская рыбалка",
        avatar: "https://i.pravatar.cc/100?img=9"
      },
      tags: ["море", "лодка", "кефаль"],
      price: 3000,
      currency: "RUB"
    },
    {
      title: "Фидерная ловля для начинающих",
      description: "Базовый курс по фидерной ловле. Снасти, прикормка, техника",
      location: "Река Волга, участок у Казани",
      coordinates: { lat: 55.8000, lng: 49.1000 },
      maxParticipants: 15,
      currentParticipants: 10,
      organizer: {
        id: 7,
        name: "Фидерная ловля",
        avatar: "https://i.pravatar.cc/100?img=10"
      },
      tags: ["фидер", "обучение", "прикормка"],
      price: 1200,
      currency: "RUB"
    },
    {
      title: "Нахлыстовая ловля форели",
      description: "Специализированный курс по нахлыстовой ловле форели",
      location: "Река Клязьма",
      coordinates: { lat: 56.0000, lng: 40.0000 },
      maxParticipants: 8,
      currentParticipants: 5,
      organizer: {
        id: 8,
        name: "Нахлыст",
        avatar: "https://i.pravatar.cc/100?img=11"
      },
      tags: ["нахлыст", "форель", "мушка"],
      price: 2500,
      currency: "RUB"
    },
    {
      title: "Подводная охота в Астрахани",
      description: "Подводная охота на сома в дельте Волги",
      location: "Астрахань, дельта Волги",
      coordinates: { lat: 46.3000, lng: 48.0000 },
      maxParticipants: 6,
      currentParticipants: 4,
      organizer: {
        id: 9,
        name: "Подводная охота",
        avatar: "https://i.pravatar.cc/100?img=12"
      },
      tags: ["подводная охота", "сом", "акваланг"],
      price: 5000,
      currency: "RUB"
    },
    {
      title: "Ремонт и настройка снастей",
      description: "Практический мастер-класс по ремонту и настройке рыболовных снастей",
      location: "Рыболовный магазин 'Снасти'",
      coordinates: { lat: 55.7700, lng: 37.6300 },
      maxParticipants: 20,
      currentParticipants: 14,
      organizer: {
        id: 10,
        name: "Рыболовные снасти",
        avatar: "https://i.pravatar.cc/100?img=13"
      },
      tags: ["ремонт", "настройка", "снасти"],
      price: 600,
      currency: "RUB"
    }
  ];

  return eventData.map((event, index) => ({
    id: index + 1,
    ...event,
    image: `https://picsum.photos/400/300?random=${30 + index}`,
    date: new Date(Date.now() + Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString()
  }));
};

export const mockEvents = generateEvents();

// Генерируем 10 групп
const generateGroups = () => {
  const groupData = [
    {
      name: "Спиннингисты Москвы",
      description: "Группа для любителей спиннинговой ловли в Москве и области",
      tags: ["спиннинг", "Москва", "хищная рыба"],
      memberCount: 1250,
      isJoined: true
    },
    {
      name: "Поплавочники",
      description: "Сообщество любителей поплавочной ловли",
      tags: ["поплавок", "мирная рыба", "обучение"],
      memberCount: 890,
      isJoined: false
    },
    {
      name: "Зимняя рыбалка",
      description: "Все о зимней рыбалке: снасти, техники, места",
      tags: ["зимняя рыбалка", "мормышка", "лед"],
      memberCount: 2100,
      isJoined: true
    },
    {
      name: "Карпятники России",
      description: "Сообщество любителей карповой ловли",
      tags: ["карп", "бойлы", "фидер"],
      memberCount: 1800,
      isJoined: false
    },
    {
      name: "Ночная рыбалка",
      description: "Группа для любителей ночной рыбалки",
      tags: ["ночная рыбалка", "сом", "донка"],
      memberCount: 750,
      isJoined: true
    },
    {
      name: "Морская рыбалка",
      description: "Рыбалка в море и океане",
      tags: ["море", "океан", "морская рыба"],
      memberCount: 650,
      isJoined: false
    },
    {
      name: "Фидерная ловля",
      description: "Сообщество любителей фидерной ловли",
      tags: ["фидер", "кормушка", "мирная рыба"],
      memberCount: 1200,
      isJoined: true
    },
    {
      name: "Нахлыст",
      description: "Группа для любителей нахлыстовой ловли",
      tags: ["нахлыст", "мушка", "форель"],
      memberCount: 450,
      isJoined: false
    },
    {
      name: "Подводная охота",
      description: "Сообщество подводных охотников",
      tags: ["подводная охота", "акваланг", "подводное плавание"],
      memberCount: 320,
      isJoined: true
    },
    {
      name: "Рыболовные снасти",
      description: "Обсуждение и обмен опытом по снастям",
      tags: ["снасти", "оборудование", "ремонт"],
      memberCount: 950,
      isJoined: false
    }
  ];

  return groupData.map((group, index) => ({
    id: index + 1,
    ...group,
    image: `https://picsum.photos/400/200?random=${20 + index}`,
    lastActivity: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000).toISOString()
  }));
};

export const mockGroups = generateGroups();

// Генерируем 100 уловов
const generateCatches = () => {
  const fishSpecies = [
    { id: 1, name: "Щука", slug: "pike", scientific_name: "Esox lucius" },
    { id: 2, name: "Окунь", slug: "perch", scientific_name: "Perca fluviatilis" },
    { id: 3, name: "Карп", slug: "carp", scientific_name: "Cyprinus carpio" },
    { id: 4, name: "Лещ", slug: "bream", scientific_name: "Abramis brama" },
    { id: 5, name: "Судак", slug: "pike-perch", scientific_name: "Sander lucioperca" },
    { id: 6, name: "Сом", slug: "catfish", scientific_name: "Silurus glanis" },
    { id: 7, name: "Плотва", slug: "roach", scientific_name: "Rutilus rutilus" },
    { id: 8, name: "Карась", slug: "crucian", scientific_name: "Carassius carassius" },
    { id: 9, name: "Линь", slug: "tench", scientific_name: "Tinca tinca" },
    { id: 10, name: "Язь", slug: "ide", scientific_name: "Leuciscus idus" }
  ];

  const locations = [
    { id: 1, name: "Озеро Светлое", lat: 55.7558, lng: 37.6176 },
    { id: 2, name: "Река Москва", lat: 55.7600, lng: 37.6200 },
    { id: 3, name: "Пруд 'Золотая рыбка'", lat: 55.7500, lng: 37.6100 },
    { id: 4, name: "Озеро Байкал", lat: 53.2001, lng: 107.8000 },
    { id: 5, name: "Река Волга", lat: 55.7558, lng: 37.6176 },
    { id: 6, name: "Озеро Селигер", lat: 57.2000, lng: 33.1000 },
    { id: 7, name: "Река Дон", lat: 47.2000, lng: 40.1000 },
    { id: 8, name: "Озеро Ладожское", lat: 60.8000, lng: 31.5000 },
    { id: 9, name: "Река Нева", lat: 59.9000, lng: 30.3000 },
    { id: 10, name: "Озеро Онежское", lat: 61.7000, lng: 35.4000 }
  ];

  const names = [
    "Алексей Петров", "Мария Сидорова", "Дмитрий Козлов", "Елена Волкова", "Сергей Морозов",
    "Анна Кузнецова", "Иван Соколов", "Ольга Лебедева", "Николай Новиков", "Татьяна Морозова",
    "Андрей Петров", "Наталья Волкова", "Михаил Соколов", "Екатерина Кузнецова", "Владимир Лебедев",
    "Ирина Новикова", "Александр Морозов", "Светлана Петрова", "Юрий Волков", "Людмила Соколова"
  ];

  const usernames = [
    "alex_fisher", "maria_angler", "dmitry_fish", "elena_catch", "sergey_hook",
    "anna_bait", "ivan_line", "olga_net", "nikolay_rod", "tatyana_reel",
    "andrey_spin", "natalia_float", "mikhail_sinker", "ekaterina_lure", "vladimir_swivel",
    "irina_hook", "alexander_line", "svetlana_bait", "yury_rod", "lyudmila_reel"
  ];

  const notes = [
    "Отличная поклевка! Рыба сопротивлялась 10 минут.",
    "Красивый экземпляр, пойман на воблер.",
    "Трофейная рыба! Борьба длилась 15 минут.",
    "Пойман на мормышку с мотылем.",
    "Отличный улов на спиннинг!",
    "Рыба клюнула на бойлы.",
    "Пойман на поплавок с червем.",
    "Красивый экземпляр на донку.",
    "Отличная поклевка на живца.",
    "Трофейная рыба на фидер!"
  ];

  const catches = [];
  
  for (let i = 1; i <= 100; i++) {
    const fish = fishSpecies[Math.floor(Math.random() * fishSpecies.length)];
    const location = locations[Math.floor(Math.random() * locations.length)];
    const name = names[Math.floor(Math.random() * names.length)];
    const username = usernames[Math.floor(Math.random() * usernames.length)];
    const note = notes[Math.floor(Math.random() * notes.length)];
    
    const weight = Math.floor(Math.random() * 5000) + 200; // 200-5200г
    const length = Math.floor(Math.random() * 80) + 20; // 20-100см
    const likes = Math.floor(Math.random() * 100);
    const comments = Math.floor(Math.random() * 20);
    const hasAdditionalPhotos = Math.random() > 0.7;
    
    catches.push({
      id: i,
      user: {
        id: Math.floor(Math.random() * 20) + 1,
        name: name,
        username: username,
        photo_url: `https://i.pravatar.cc/100?img=${(i % 20) + 1}`,
        role: "user" as const,
        created_at: "2024-01-01T00:00:00Z"
      },
      fish_species: fish,
      weight: weight,
      length: length,
      photo_url: `https://picsum.photos/400/300?random=${i}`,
      additional_photos: hasAdditionalPhotos ? JSON.stringify([
        `https://picsum.photos/400/300?random=${i + 1000}`,
        `https://picsum.photos/400/300?random=${i + 2000}`
      ]) : undefined,
      point: {
        id: location.id,
        name: location.name,
        latitude: location.lat,
        longitude: location.lng
      },
      notes: note,
      created_at: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000).toISOString(),
      likes_count: likes,
      comments_count: comments,
      is_liked: Math.random() > 0.7,
      type: "catch" as const,
      lat: location.lat,
      lng: location.lng,
      privacy: "all" as const
    });
  }
  
  return catches;
};

export const mockCatches = generateCatches();

export const mockTracks = [
  {
    id: 1,
    name: "Утренняя рыбалка на озере",
    description: "Ранняя рыбалка на щуку с использованием спиннинга",
    status: "completed" as const,
    started_at: "2024-04-12T06:00:00Z",
    ended_at: "2024-04-12T12:00:00Z",
    duration_minutes: 360,
    total_distance: 2.5,
    total_catches: 3,
    total_weight: 4200,
    average_weight: 1400,
    track_points: [
      { lat: 55.7558, lng: 37.6176, timestamp: "2024-04-12T06:00:00Z" },
      { lat: 55.7560, lng: 37.6178, timestamp: "2024-04-12T07:00:00Z" },
      { lat: 55.7562, lng: 37.6180, timestamp: "2024-04-12T08:00:00Z" },
      { lat: 55.7564, lng: 37.6182, timestamp: "2024-04-12T09:00:00Z" },
      { lat: 55.7566, lng: 37.6184, timestamp: "2024-04-12T10:00:00Z" },
      { lat: 55.7568, lng: 37.6186, timestamp: "2024-04-12T11:00:00Z" },
      { lat: 55.7570, lng: 37.6188, timestamp: "2024-04-12T12:00:00Z" }
    ],
    user: {
      id: 1,
      name: "Алексей Петров",
      username: "alex_fisher",
      avatar_url: "https://i.pravatar.cc/100?img=7"
    },
    place: {
      id: 1,
      name: "Озеро Светлое",
      coordinates: { lat: 55.7558, lng: 37.6176 }
    }
  },
  {
    id: 2,
    name: "Зимняя рыбалка на реке",
    description: "Ловля окуня на мормышку в зимний период",
    status: "active" as const,
    started_at: "2024-04-13T08:00:00Z",
    ended_at: null,
    duration_minutes: 180,
    total_distance: 1.2,
    total_catches: 1,
    total_weight: 800,
    average_weight: 800,
    track_points: [
      { lat: 55.7600, lng: 37.6200, timestamp: "2024-04-13T08:00:00Z" },
      { lat: 55.7602, lng: 37.6202, timestamp: "2024-04-13T09:00:00Z" },
      { lat: 55.7604, lng: 37.6204, timestamp: "2024-04-13T10:00:00Z" },
      { lat: 55.7606, lng: 37.6206, timestamp: "2024-04-13T11:00:00Z" }
    ],
    user: {
      id: 2,
      name: "Мария Сидорова",
      username: "maria_angler",
      avatar_url: "https://i.pravatar.cc/100?img=8"
    },
    place: {
      id: 2,
      name: "Река Москва",
      coordinates: { lat: 55.7600, lng: 37.6200 }
    }
  }
];

export const mockWeatherPoints = [
  {
    id: 1,
    name: "Озеро Светлое",
    coordinates: { lat: 55.7558, lng: 37.6176 },
    weather: {
      temperature: 15,
      humidity: 65,
      windSpeed: 12,
      pressure: 1013,
      description: "Переменная облачность",
      icon: "partly-cloudy"
    }
  },
  {
    id: 2,
    name: "Река Москва",
    coordinates: { lat: 55.7600, lng: 37.6200 },
    weather: {
      temperature: 18,
      humidity: 70,
      windSpeed: 8,
      pressure: 1015,
      description: "Ясно",
      icon: "sunny"
    }
  }
];
