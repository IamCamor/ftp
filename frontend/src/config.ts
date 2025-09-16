type AuthProviders = {
  passwordForm: boolean;
  google: boolean;
  vk: boolean;
  yandex: boolean;
  apple: boolean;
};

type FeatureFlags = {
  auth: {
    enabled: boolean;
    providers: AuthProviders;
    requireAuthForWeatherSave: boolean;
    links: {
      offer: string;
      personalData: string;
      rules: string;
    };
  };
  banners: boolean;
  ratings: boolean;
  bonusProgram: boolean;
  glassUi: boolean;
  debug: {
    enableConsoleLogs: boolean;
    enableDataLogging: boolean;
  };
};

const config = {
  apiBase: 'https://api.fishtrackpro.ru/api/v1',
  siteBase: 'https://fishtrackpro.ru',
  assetsBase: 'https://fishtrackpro.ru/assets',
  logoUrl: '/logo.png',
  defaultAvatar: '/default-avatar.png',
  glassEnabled: true,
  feedEvery: 60000,
  map: {
    enabled: true,
    provider: 'osm', // 'osm' | 'google' | 'yandex'
    defaultCenter: { lat: 55.751244, lng: 37.618423 },
    defaultZoom: 10,
    maxZoom: 19,
    minZoom: 1,
    maxPoints: 1000,
    tiles: {
      osm: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    },
    features: {
      clickToSelect: true,
      dragMarker: true,
      currentLocation: true,
      zoomControls: true,
      fullscreen: false
    },
    icons: {
      catch: {
        url: '/icons/fish-pin.svg',
        size: [32, 32],
        anchor: [16, 32],
        popupAnchor: [0, -32]
      },
      point: {
        url: '/icons/location-pin.svg',
        size: [28, 28],
        anchor: [14, 28],
        popupAnchor: [0, -28]
      },
      weather: {
        url: '/icons/weather-pin.svg',
        size: [24, 24],
        anchor: [12, 24],
        popupAnchor: [0, -24]
      }
    }
  },
  features: {
    auth: {
      enabled: true,
      providers: {
        passwordForm: true,
        google: true,
        vk: true,
        yandex: true,
        apple: false
      },
      requireAuthForWeatherSave: true,
      links: {
        offer: 'https://www.fishtrackpro.ru/docs/offer',
        personalData: 'https://www.fishtrackpro.ru/docs/personal-data',
        rules: 'https://www.fishtrackpro.ru/docs/rules',
      },
    },
    banners: true,
    ratings: true,
    bonusProgram: true,
    glassUi: true,
    debug: {
      enableConsoleLogs: true,
      enableDataLogging: false, // Отключаем логирование массивов данных
    },
  } as FeatureFlags,
  routes: {
    feed: '/feed',
    map: '/map',
    addCatch: '/add/catch',
    addPlace: '/add/place',
    alerts: '/alerts',
    profile: '/profile',
    weather: '/weather',
    catchDetail: (id: number | string) => `/catch/${id}`,
    placeDetail: (id: number | string) => `/place/${id}`,
    auth: {
      login: '/auth/login',
      register: '/auth/register'
    },
  },
};

export default config;

