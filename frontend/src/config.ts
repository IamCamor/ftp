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
};

const config = {
  apiBase: 'https://api.fishtrackpro.ru/api/v1',
  siteBase: 'https://www.fishtrackpro.ru',
  assetsBase: 'https://www.fishtrackpro.ru/assets',
  logoUrl: '/logo.svg',
  defaultAvatar: '/default-avatar.png',
  glassEnabled: true,
  feedEvery: 60000,
  map: {
    defaultCenter: { lat: 55.751244, lng: 37.618423 },
    defaultZoom: 10,
    tiles: {
      url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    },
    maxPoints: 1000,
  },
  features: {
    auth: {
      enabled: true,
      providers: {
        passwordForm: true,
        google: true,
        vk: true,
        yandex: true,
        apple: true
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

