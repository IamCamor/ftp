import type { TestData } from '../types';

export const testData: TestData = {
  users: [
    {
      id: 1,
      name: "Алексей Рыбаков",
      username: "alex_fisher",
      email: "alex@example.com",
      photo_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face",
      role: "pro",
      is_premium: true,
      premium_expires_at: "2025-12-31T23:59:59Z",
      crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
      bonus_balance: 1250,
      followers_count: 1247,
      following_count: 89,
      total_likes_received: 15680,
      is_online: true,
      total_bonuses: 1250,
      average_rating: 4.8,
      bio: "Профессиональный рыболов с 15-летним стажем. Специализируюсь на спиннинге и нахлысте.",
      location: "Москва, Россия",
      website: "https://alexfisher.ru",
      is_guide: true,
      guide_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
      guide_info: "Провожу индивидуальные и групповые туры по рыбалке",
      guide_website: "https://alexfisher.ru/guide",
      guide_rating: 4.9,
      guide_reviews_count: 127,
      privacy_settings: {
        allow_friend_requests: true,
        allow_follow_notifications: true,
        show_online_status: true,
        show_last_seen: true
      },
      created_at: "2023-01-15T10:30:00Z"
    },
    {
      id: 2,
      name: "Мария Волкова",
      username: "maria_fish",
      email: "maria@example.com",
      photo_url: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face",
      role: "premium",
      is_premium: true,
      premium_expires_at: "2025-06-30T23:59:59Z",
      crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
      bonus_balance: 890,
      followers_count: 892,
      following_count: 156,
      total_likes_received: 9876,
      is_online: false,
      last_seen_at: "2025-01-13T18:45:00Z",
      total_bonuses: 890,
      average_rating: 4.6,
      bio: "Люблю рыбалку на поплавок и фидер. Часто рыбачу на Волге и Оке.",
      location: "Нижний Новгород, Россия",
      privacy_settings: {
        allow_friend_requests: true,
        allow_follow_notifications: false,
        show_online_status: true,
        show_last_seen: true
      },
      created_at: "2023-03-22T14:20:00Z"
    },
    {
      id: 3,
      name: "Дмитрий Соколов",
      username: "dmitry_angler",
      email: "dmitry@example.com",
      photo_url: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face",
      role: "user",
      is_premium: false,
      bonus_balance: 340,
      followers_count: 234,
      following_count: 67,
      total_likes_received: 2341,
      is_online: true,
      total_bonuses: 340,
      average_rating: 4.2,
      bio: "Начинающий рыболов, изучаю разные техники ловли",
      location: "Санкт-Петербург, Россия",
      privacy_settings: {
        allow_friend_requests: false,
        allow_follow_notifications: true,
        show_online_status: false,
        show_last_seen: false
      },
      created_at: "2024-01-10T09:15:00Z"
    },
    {
      id: 4,
      name: "Елена Петрова",
      username: "elena_fishing",
      email: "elena@example.com",
      photo_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face",
      role: "pro",
      is_premium: true,
      premium_expires_at: "2025-08-15T23:59:59Z",
      crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
      bonus_balance: 2100,
      followers_count: 2156,
      following_count: 198,
      total_likes_received: 23450,
      is_online: true,
      total_bonuses: 2100,
      average_rating: 4.9,
      bio: "Мастер спорта по рыбной ловле. Специализация: карпфишинг и фидер",
      location: "Казань, Россия",
      website: "https://elenafishing.com",
      is_guide: true,
      guide_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
      guide_info: "Опытный гид по карпфишингу. Провожу мастер-классы",
      guide_website: "https://elenafishing.com/guide",
      guide_rating: 4.95,
      guide_reviews_count: 89,
      privacy_settings: {
        allow_friend_requests: true,
        allow_follow_notifications: true,
        show_online_status: true,
        show_last_seen: true
      },
      created_at: "2022-11-05T16:45:00Z"
    }
  ],

  points: [
    {
      id: 1,
      user: {
        id: 1,
        name: "Алексей Рыбаков",
        username: "alex_fisher",
        photo_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face",
        role: "pro",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 1247,
        is_online: true,
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: true,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2023-01-15T10:30:00Z"
      },
      lat: 55.7558,
      lng: 37.6176,
      title: "Озеро Сенеж",
      description: "Красивое озеро с чистой водой. Отличное место для ловли щуки и окуня. Есть удобные подходы к воде.",
      cover_url: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop",
      privacy: "all",
      media_count: 12,
      media: [
        {
          id: 1,
          url: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop",
          created_at: "2025-01-10T08:30:00Z"
        },
        {
          id: 2,
          url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
          created_at: "2025-01-10T08:35:00Z"
        }
      ],
      catches_count: 45,
      species: "Щука, Окунь, Плотва",
      weather_data: {
        temperature: 15,
        pressure: 760,
        wind_speed: 3,
        cloudiness: "малооблачно"
      },
      is_paid_place: false,
      has_security: false,
      has_parking: true,
      place_type: "lake",
      depth: 8,
      water_quality: "excellent",
      accessibility: "easy",
      facilities: ["парковка", "беседки", "туалет"],
      created_at: "2025-01-10T08:00:00Z",
      type: "point"
    },
    {
      id: 2,
      user: {
        id: 2,
        name: "Мария Волкова",
        username: "maria_fish",
        photo_url: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face",
        role: "premium",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 892,
        is_online: false,
        last_seen_at: "2025-01-13T18:45:00Z",
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: false,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2023-03-22T14:20:00Z"
      },
      lat: 56.3269,
      lng: 44.0075,
      title: "Волга у Нижнего Новгорода",
      description: "Классическое место для ловли на фидер. Хорошие подходы, много рыбы. Особенно хорошо клюет лещ и плотва.",
      cover_url: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop",
      privacy: "all",
      media_count: 8,
      media: [
        {
          id: 3,
          url: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop",
          created_at: "2025-01-12T06:00:00Z"
        },
        {
          id: 4,
          url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
          created_at: "2025-01-12T06:15:00Z"
        }
      ],
      catches_count: 67,
      species: "Лещ, Плотва, Густера, Щука",
      weather_data: {
        temperature: 12,
        pressure: 765,
        wind_speed: 2,
        cloudiness: "ясно"
      },
      is_paid_place: false,
      has_security: false,
      has_parking: true,
      place_type: "river",
      depth: 12,
      water_quality: "good",
      accessibility: "easy",
      facilities: ["парковка", "спуск к воде"],
      created_at: "2025-01-12T05:30:00Z",
      type: "point"
    },
    {
      id: 3,
      user: {
        id: 4,
        name: "Елена Петрова",
        username: "elena_fishing",
        photo_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face",
        role: "pro",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 2156,
        is_online: true,
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: true,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2022-11-05T16:45:00Z"
      },
      lat: 55.8304,
      lng: 49.0661,
      title: "Платный карповый пруд 'Золотая рыбка'",
      description: "Премиум место для карпфишинга. Большие карпы, отличная инфраструктура. Требуется предварительная запись.",
      cover_url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
      privacy: "all",
      media_count: 15,
      media: [
        {
          id: 5,
          url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
          created_at: "2025-01-08T10:00:00Z"
        },
        {
          id: 6,
          url: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop",
          created_at: "2025-01-08T10:10:00Z"
        }
      ],
      catches_count: 23,
      species: "Карп, Амур, Толстолобик",
      weather_data: {
        temperature: 18,
        pressure: 758,
        wind_speed: 1,
        cloudiness: "ясно"
      },
      is_paid_place: true,
      has_security: true,
      has_parking: true,
      place_type: "pond",
      depth: 15,
      water_quality: "excellent",
      accessibility: "easy",
      facilities: ["парковка", "охрана", "беседки", "туалет", "магазин", "кафе"],
      created_at: "2025-01-08T09:00:00Z",
      type: "point"
    }
  ],

  catches: [
    {
      id: 1,
      type: "catch",
      user: {
        id: 1,
        name: "Алексей Рыбаков",
        username: "alex_fisher",
        photo_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face",
        role: "pro",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 1247,
        is_online: true,
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: true,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2023-01-15T10:30:00Z"
      },
      lat: 55.7558,
      lng: 37.6176,
      species: "Щука",
      length: 68,
      weight: 3.2,
      style: "спиннинг",
      lure: "воблер Salmo Hornet",
      tackle: "Shimano Stradic 2500",
      notes: "Отличная поклевка на рассвете. Рыба активно атаковала воблер.",
      photos: [
        "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop",
        "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop"
      ],
      main_photo: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop",
      photo_url: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop",
      additional_photos: JSON.stringify([
        "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop"
      ]),
      privacy: "all",
      caught_at: "2025-01-14T06:30:00Z",
      likes_count: 47,
      comments_count: 8,
      liked_by_me: false,
      point: {
        id: 1,
        name: "Озеро Сенеж",
        latitude: 55.7558,
        longitude: 37.6176
      },
      fish_species: {
        id: 1,
        name: "Щука",
        slug: "pike"
      },
      fishing_method: {
        id: 1,
        name: "Спиннинг",
        slug: "spinning"
      },
      fishing_location: {
        id: 1,
        name: "Озеро",
        slug: "lake"
      },
      weather: {
        temperature: "8.5",
        pressure: 765,
        wind_speed: 2.5,
        cloudiness: "малооблачно",
        precipitation: "без осадков",
        wind_direction: "СВ"
      },
      created_at: "2025-01-14T06:45:00Z"
    },
    {
      id: 2,
      type: "catch",
      user: {
        id: 2,
        name: "Мария Волкова",
        username: "maria_fish",
        photo_url: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face",
        role: "premium",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 892,
        is_online: false,
        last_seen_at: "2025-01-13T18:45:00Z",
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: false,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2023-03-22T14:20:00Z"
      },
      lat: 56.3269,
      lng: 44.0075,
      species: "Лещ",
      length: 45,
      weight: 1.8,
      style: "фидер",
      lure: "червь + опарыш",
      tackle: "Daiwa Crosscast 3000",
      notes: "Клевал на червя с опарышем. Хорошая активность с утра.",
      photos: [
        "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop"
      ],
      main_photo: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop",
      photo_url: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop",
      privacy: "all",
      caught_at: "2025-01-13T07:15:00Z",
      likes_count: 23,
      comments_count: 5,
      liked_by_me: true,
      point: {
        id: 2,
        name: "Волга у Нижнего Новгорода",
        latitude: 56.3269,
        longitude: 44.0075
      },
      fish_species: {
        id: 2,
        name: "Лещ",
        slug: "bream"
      },
      fishing_method: {
        id: 2,
        name: "Фидер",
        slug: "feeder"
      },
      fishing_location: {
        id: 2,
        name: "Река",
        slug: "river"
      },
      weather: {
        temperature: "12.0",
        pressure: 768,
        wind_speed: 1.8,
        cloudiness: "ясно",
        precipitation: "без осадков",
        wind_direction: "Ю"
      },
      created_at: "2025-01-13T07:30:00Z"
    },
    {
      id: 3,
      type: "catch",
      user: {
        id: 4,
        name: "Елена Петрова",
        username: "elena_fishing",
        photo_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face",
        role: "pro",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 2156,
        is_online: true,
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: true,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2022-11-05T16:45:00Z"
      },
      lat: 55.8304,
      lng: 49.0661,
      species: "Карп",
      length: 85,
      weight: 12.5,
      style: "карпфишинг",
      lure: "бойлы со вкусом клубники",
      tackle: "Fox Warrior 3.5lb",
      notes: "Трофейный карп! Боролся 15 минут. Отличная рыбалка на платнике.",
      photos: [
        "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
        "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop"
      ],
      main_photo: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
      photo_url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
      additional_photos: JSON.stringify([
        "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=600&fit=crop"
      ]),
      privacy: "all",
      caught_at: "2025-01-12T14:20:00Z",
      likes_count: 89,
      comments_count: 15,
      liked_by_me: false,
      point: {
        id: 3,
        name: "Платный карповый пруд 'Золотая рыбка'",
        latitude: 55.8304,
        longitude: 49.0661
      },
      fish_species: {
        id: 3,
        name: "Карп",
        slug: "carp"
      },
      fishing_method: {
        id: 3,
        name: "Карпфишинг",
        slug: "carpfishing"
      },
      fishing_location: {
        id: 3,
        name: "Платный пруд",
        slug: "paid_pond"
      },
      weather: {
        temperature: "16.5",
        pressure: 760,
        wind_speed: 2.2,
        cloudiness: "малооблачно",
        precipitation: "без осадков",
        wind_direction: "З"
      },
      created_at: "2025-01-12T14:35:00Z"
    }
  ],

  events: [
    {
      id: 1,
      title: "Турнир по спиннингу 'Весенний клев'",
      description: "Ежегодный турнир по ловле щуки и окуня на спиннинг. Призовой фонд 50,000 рублей.",
      lat: 55.7558,
      lng: 37.6176,
      location_name: "Озеро Сенеж, Московская область",
      start_at: "2025-04-15T06:00:00Z",
      end_at: "2025-04-15T18:00:00Z",
      max_participants: 50,
      status: "published",
      organizer_id: 1,
      cover_url: "https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=800&h=400&fit=crop",
      type: "tournament",
      organizer: {
        id: 1,
        name: "Алексей Рыбаков",
        username: "alex_fisher",
        photo_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face",
        role: "pro",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 1247,
        is_online: true,
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: true,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2023-01-15T10:30:00Z"
      },
      participants_count: 23,
      is_participant: false,
      is_organizer: false,
      created_at: "2025-01-10T10:00:00Z",
      updated_at: "2025-01-10T10:00:00Z"
    },
    {
      id: 2,
      title: "Мастер-класс по карпфишингу",
      description: "Практический мастер-класс по ловле карпа. Покажем все секреты и тонкости.",
      lat: 55.8304,
      lng: 49.0661,
      location_name: "Платный карповый пруд 'Золотая рыбка', Казань",
      start_at: "2025-03-20T09:00:00Z",
      end_at: "2025-03-20T17:00:00Z",
      max_participants: 15,
      status: "published",
      organizer_id: 4,
      cover_url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=400&fit=crop",
      type: "masterclass",
      organizer: {
        id: 4,
        name: "Елена Петрова",
        username: "elena_fishing",
        photo_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face",
        role: "pro",
        is_premium: true,
        crown_icon_url: "https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=24&h=24&fit=crop",
        followers_count: 2156,
        is_online: true,
        privacy_settings: {
          allow_friend_requests: true,
          allow_follow_notifications: true,
          show_online_status: true,
          show_last_seen: true
        },
        created_at: "2022-11-05T16:45:00Z"
      },
      participants_count: 8,
      is_participant: true,
      is_organizer: false,
      created_at: "2025-01-08T14:30:00Z",
      updated_at: "2025-01-08T14:30:00Z"
    }
  ]
};

export default testData;
