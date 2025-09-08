# Система подписок и социальных функций FishTrackPro

## Обзор

Система подписок позволяет пользователям следить за уловами других рыбаков, создавая социальную сеть внутри приложения. Включает в себя подсчет лайков, подписчиков, фильтрацию ленты и онлайн-статус пользователей.

## Основные функции

### 1. Система подписок
- **Подписка на пользователей**: Возможность подписаться на других рыбаков
- **Подсчет статистики**: Автоматический подсчет подписчиков, подписок и лайков
- **Взаимные подписки**: Отображение общих подписок между пользователями

### 2. Фильтрация ленты
- **Общая лента**: Все уловы в хронологическом порядке
- **Лента подписок**: Только уловы пользователей, на которых подписан
- **Локальная лента**: Уловы поблизости от текущего местоположения

### 3. Медиа-поддержка
- **Фото**: До 10 фото для обычных пользователей, до 20 для Pro/Premium
- **Видео**: До 1 видео для Pro и Premium пользователей
- **Главное медиа**: Автоматический выбор главного фото/видео

### 4. Онлайн-статус
- **Индикатор онлайн**: Пульсирующая зеленая точка для онлайн пользователей
- **Последняя активность**: Отображение времени последнего входа
- **Пульсирующие метки**: На карте показываются онлайн рыбаки

## Техническая реализация

### Backend (Laravel)

#### Миграции
```sql
-- Таблица подписок
CREATE TABLE follows (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    follower_id BIGINT NOT NULL,
    following_id BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Статистика пользователей
ALTER TABLE users ADD COLUMN followers_count INT DEFAULT 0;
ALTER TABLE users ADD COLUMN following_count INT DEFAULT 0;
ALTER TABLE users ADD COLUMN total_likes_received INT DEFAULT 0;
ALTER TABLE users ADD COLUMN last_seen_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN is_online BOOLEAN DEFAULT FALSE;

-- Медиа в уловах
ALTER TABLE catch_records ADD COLUMN photos JSON NULL;
ALTER TABLE catch_records ADD COLUMN videos JSON NULL;
ALTER TABLE catch_records ADD COLUMN main_photo VARCHAR(255) NULL;
ALTER TABLE catch_records ADD COLUMN main_video VARCHAR(255) NULL;
ALTER TABLE catch_records ADD COLUMN media_count INT DEFAULT 0;
```

#### Модели

**Follow.php**
```php
class Follow extends Model
{
    protected $fillable = ['follower_id', 'following_id'];
    
    public function user() {
        return $this->belongsTo(User::class, 'following_id');
    }
    
    public function follower() {
        return $this->belongsTo(User::class, 'follower_id');
    }
}
```

**User.php** (обновления)
```php
// Новые поля
protected $fillable = [
    // ... существующие поля
    'followers_count',
    'following_count', 
    'total_likes_received',
    'last_seen_at',
    'is_online'
];

// Отношения
public function following() {
    return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
}

public function followers() {
    return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
}

// Методы
public function follow(User $user): bool {
    if ($this->isFollowing($user)) return false;
    
    Follow::create([
        'follower_id' => $this->id,
        'following_id' => $user->id
    ]);
    
    $this->increment('following_count');
    $user->increment('followers_count');
    
    return true;
}

public function updateOnlineStatus(bool $isOnline = true): bool {
    $this->update([
        'is_online' => $isOnline,
        'last_seen_at' => now()
    ]);
    
    return true;
}
```

**CatchRecord.php** (обновления)
```php
// Новые поля
protected $fillable = [
    // ... существующие поля
    'photos',
    'videos', 
    'main_photo',
    'main_video',
    'media_count'
];

protected $casts = [
    'photos' => 'array',
    'videos' => 'array'
];

// Методы для работы с медиа
public function getMediaLimits(): array {
    $role = $this->user->isPremium() ? 'premium' : 
            ($this->user->isPro() ? 'pro' : 'user');
    
    return config("media.limits.{$role}");
}

public function canAddPhotos(int $count = 1): bool {
    $limits = $this->getMediaLimits();
    $currentPhotos = count($this->photos ?? []);
    
    return ($currentPhotos + $count) <= $limits['max_photos'];
}
```

#### Контроллеры

**FollowController.php**
```php
class FollowController extends Controller
{
    public function follow(Request $request): JsonResponse {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $user = User::findOrFail($request->user_id);
        $currentUser = Auth::user();
        
        if ($currentUser->follow($user)) {
            return response()->json([
                'success' => true,
                'message' => 'Подписка оформлена',
                'is_following' => true
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Ошибка подписки'
        ], 400);
    }
    
    public function followers(Request $request, User $user): JsonResponse {
        $followers = $user->followers()
            ->with(['avatar'])
            ->paginate(20);
            
        return response()->json($followers);
    }
}
```

**FeedController.php** (обновления)
```php
public function index(Request $request): JsonResponse {
    $type = $request->get('type', 'all');
    $latitude = $request->get('latitude');
    $longitude = $request->get('longitude');
    $radius = $request->get('radius', 50); // км
    
    $query = CatchRecord::with(['user', 'point', 'fishSpecies', 'fishingMethod'])
        ->where('is_approved', true);
    
    switch ($type) {
        case 'following':
            $followingIds = Auth::user()->following()->pluck('users.id');
            $query->whereIn('user_id', $followingIds);
            break;
            
        case 'nearby':
            if ($latitude && $longitude) {
                $query->whereHas('point', function($q) use ($latitude, $longitude, $radius) {
                    $q->whereRaw("ST_Distance_Sphere(
                        POINT(longitude, latitude), 
                        POINT(?, ?)
                    ) <= ?", [$longitude, $latitude, $radius * 1000]);
                });
            }
            break;
    }
    
    $catches = $query->orderBy('created_at', 'desc')
        ->paginate(20);
    
    $transformedCatches = $catches->map(function($catch) {
        return $this->transformCatch($catch, $catch->user);
    });
    
    return response()->json([
        'data' => $transformedCatches,
        'current_page' => $catches->currentPage(),
        'last_page' => $catches->lastPage(),
        'per_page' => $catches->perPage(),
        'total' => $catches->total()
    ]);
}

private function transformCatch(CatchRecord $catch, User $user): array {
    return [
        'id' => $catch->id,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => $user->avatar,
            'is_online' => $user->is_online,
            'last_seen_at' => $user->last_seen_at,
            'followers_count' => $user->followers_count,
            'crown_icon_url' => $user->getCrownIconUrl(),
            'is_premium' => $user->isPremium()
        ],
        'photos' => $catch->photos,
        'videos' => $catch->videos,
        'main_photo' => $catch->main_photo,
        'main_video' => $catch->main_video,
        'media_count' => $catch->media_count,
        // ... остальные поля
    ];
}
```

**OnlineStatusController.php**
```php
class OnlineStatusController extends Controller
{
    public function update(Request $request): JsonResponse {
        $isOnline = $request->get('is_online', true);
        
        Auth::user()->updateOnlineStatus($isOnline);
        
        return response()->json([
            'success' => true,
            'is_online' => $isOnline,
            'last_seen_at' => Auth::user()->last_seen_at
        ]);
    }
    
    public function online(): JsonResponse {
        $onlineUsers = User::online()
            ->select(['id', 'name', 'username', 'avatar', 'last_seen_at'])
            ->get();
            
        return response()->json([
            'users' => $onlineUsers,
            'count' => $onlineUsers->count()
        ]);
    }
}
```

#### Конфигурация

**config/media.php**
```php
<?php
return [
    'limits' => [
        'user' => [
            'max_photos' => 10,
            'max_videos' => 0,
            'max_media_total' => 10,
            'video_enabled' => false,
        ],
        'pro' => [
            'max_photos' => 20,
            'max_videos' => 1,
            'max_media_total' => 21,
            'video_enabled' => true,
        ],
        'premium' => [
            'max_photos' => 20,
            'max_videos' => 1,
            'max_media_total' => 21,
            'video_enabled' => true,
        ],
    ],
];
```

### Frontend (React + TypeScript)

#### Типы

**types.ts** (обновления)
```typescript
export interface User {
  // ... существующие поля
  followers_count?: number;
  following_count?: number;
  total_likes_received?: number;
  last_seen_at?: string;
  is_online?: boolean;
}

export interface CatchRecord {
  // ... существующие поля
  photos?: string[];
  videos?: string[];
  main_photo?: string;
  main_video?: string;
  media_count?: number;
  user: {
    id: number;
    name: string;
    username?: string;
    avatar?: string;
    is_online?: boolean;
    last_seen_at?: string;
    followers_count?: number;
    crown_icon_url?: string;
    is_premium?: boolean;
  };
}

export interface FollowResponse {
  success: boolean;
  message: string;
  is_following: boolean;
}

export interface FollowersResponse {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface OnlineStatusResponse {
  success: boolean;
  is_online: boolean;
  last_seen_at: string;
}

export interface OnlineUsersResponse {
  users: User[];
  count: number;
}

export interface MediaLimits {
  max_photos: number;
  max_videos: number;
  max_media_total: number;
  video_enabled: boolean;
}
```

#### API функции

**api.ts** (обновления)
```typescript
// Система подписок
export async function followUser(userId: number): Promise<FollowResponse> {
  const response = await http.post('/follow', { user_id: userId });
  return response.data;
}

export async function unfollowUser(userId: number): Promise<FollowResponse> {
  const response = await http.post('/unfollow', { user_id: userId });
  return response.data;
}

export async function toggleFollow(userId: number): Promise<FollowResponse> {
  const response = await http.post('/follow/toggle', { user_id: userId });
  return response.data;
}

export async function getUserFollowers(userId: number, page: number = 1): Promise<FollowersResponse> {
  const response = await http.get(`/users/${userId}/followers?page=${page}`);
  return response.data;
}

export async function getUserFollowing(userId: number, page: number = 1): Promise<FollowersResponse> {
  const response = await http.get(`/users/${userId}/following?page=${page}`);
  return response.data;
}

// Онлайн-статус
export async function updateOnlineStatus(isOnline: boolean = true): Promise<OnlineStatusResponse> {
  const response = await http.post('/online/update', { is_online: isOnline });
  return response.data;
}

export async function setOffline(): Promise<OnlineStatusResponse> {
  const response = await http.post('/online/offline');
  return response.data;
}

export async function getOnlineUsers(): Promise<OnlineUsersResponse> {
  const response = await http.get('/online/users');
  return response.data;
}

// Улучшенная лента
export async function getFeed(params: {
  type?: 'all' | 'following' | 'nearby';
  latitude?: number;
  longitude?: number;
  radius?: number;
  limit?: number;
  page?: number;
} = {}): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number; } }> {
  const response = await http.get('/feed', { params });
  return response.data;
}

export async function getPersonalFeed(limit: number = 20, page: number = 1): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number; } }> {
  const response = await http.get('/feed/personal', { params: { limit, page } });
  return response.data;
}

export async function getNearbyFeed(latitude: number, longitude: number, radius: number = 50, limit: number = 20, page: number = 1): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number; } }> {
  const response = await http.get('/feed/nearby', { 
    params: { latitude, longitude, radius, limit, page } 
  });
  return response.data;
}

export async function getFollowingFeed(limit: number = 20, page: number = 1): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number; } }> {
  const response = await http.get('/feed/following', { params: { limit, page } });
  return response.data;
}
```

#### Компоненты

**FollowStats.tsx**
```typescript
interface FollowStatsProps {
  followersCount: number;
  followingCount: number;
  likesCount: number;
  onFollowersClick: () => void;
  onFollowingClick: () => void;
  onLikesClick: () => void;
}

const FollowStats: React.FC<FollowStatsProps> = ({
  followersCount,
  followingCount,
  likesCount,
  onFollowersClick,
  onFollowingClick,
  onLikesClick
}) => {
  return (
    <div className="follow-stats">
      <div className="stats-row">
        <div className="stat-item" onClick={onFollowersClick}>
          <div className="stat-number">{followersCount}</div>
          <div className="stat-label">Подписчики</div>
        </div>
        
        <div className="stat-item" onClick={onFollowingClick}>
          <div className="stat-number">{followingCount}</div>
          <div className="stat-label">Подписки</div>
        </div>
        
        <div className="stat-item" onClick={onLikesClick}>
          <div className="stat-number">{likesCount}</div>
          <div className="stat-label">Лайки</div>
        </div>
      </div>
    </div>
  );
};
```

**FollowersModal.tsx**
```typescript
interface FollowersModalProps {
  title: string;
  users: User[];
  onClose: () => void;
  onLoadMore: () => void;
  hasMore: boolean;
}

const FollowersModal: React.FC<FollowersModalProps> = ({
  title,
  users,
  onClose,
  onLoadMore,
  hasMore
}) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [filteredUsers, setFilteredUsers] = useState(users);

  useEffect(() => {
    if (searchQuery.trim()) {
      const filtered = users.filter(user => 
        user.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        user.username?.toLowerCase().includes(searchQuery.toLowerCase())
      );
      setFilteredUsers(filtered);
    } else {
      setFilteredUsers(users);
    }
  }, [searchQuery, users]);

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="followers-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>{title}</h2>
          <button className="close-button" onClick={onClose}>
            <span className="material-symbols-rounded">close</span>
          </button>
        </div>
        
        <div className="modal-content">
          <div className="search-section">
            <div className="search-input-group">
              <span className="material-symbols-rounded">search</span>
              <input
                type="text"
                className="search-input"
                placeholder="Поиск пользователей..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
          
          <div className="users-list">
            {filteredUsers.map((user) => (
              <div key={user.id} className="user-item">
                <Avatar src={user.avatar} size={40} />
                <div className="user-info">
                  <div className="user-name">
                    {user.name}
                    {user.is_premium && user.crown_icon_url && (
                      <img src={user.crown_icon_url} alt="Premium" className="crown-icon" />
                    )}
                  </div>
                  {user.username && (
                    <div className="user-username">@{user.username}</div>
                  )}
                  <div className="user-stats">
                    {user.followers_count} подписчиков
                  </div>
                </div>
              </div>
            ))}
            
            {hasMore && (
              <button className="load-more-button" onClick={onLoadMore}>
                Загрузить еще
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
```

**OnlineIndicator.tsx**
```typescript
interface OnlineIndicatorProps {
  isOnline: boolean;
  lastSeenAt?: string;
  size?: 'small' | 'medium' | 'large';
  showText?: boolean;
}

const OnlineIndicator: React.FC<OnlineIndicatorProps> = ({
  isOnline,
  lastSeenAt,
  size = 'small',
  showText = false
}) => {
  const formatLastSeen = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'только что';
    if (diffMins < 60) return `${diffMins} мин назад`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)} ч назад`;
    return `${Math.floor(diffMins / 1440)} дн назад`;
  };

  return (
    <div className={`online-indicator online-indicator-${size}`}>
      {isOnline ? (
        <>
          <div className="online-dot"></div>
          {showText && <span className="online-text">Онлайн</span>}
        </>
      ) : (
        <>
          <div className="offline-dot"></div>
          {showText && lastSeenAt && (
            <span className="offline-text">{formatLastSeen(lastSeenAt)}</span>
          )}
        </>
      )}
    </div>
  );
};
```

**FeedFilters.tsx**
```typescript
interface FeedFiltersProps {
  activeFilter: 'all' | 'following' | 'nearby';
  onFilterChange: (filter: 'all' | 'following' | 'nearby') => void;
  className?: string;
}

const FeedFilters: React.FC<FeedFiltersProps> = ({
  activeFilter,
  onFilterChange,
  className
}) => {
  const filters = [
    { key: 'all', label: 'Все', icon: 'public' },
    { key: 'following', label: 'Подписки', icon: 'people' },
    { key: 'nearby', label: 'Рядом', icon: 'location_on' }
  ] as const;

  return (
    <div className={`feed-filters ${className || ''}`}>
      <div className="filters-container">
        {filters.map((filter) => (
          <button
            key={filter.key}
            className={`filter-button ${activeFilter === filter.key ? 'active' : ''}`}
            onClick={() => onFilterChange(filter.key)}
          >
            <span className="material-symbols-rounded">{filter.icon}</span>
            <span className="filter-label">{filter.label}</span>
          </button>
        ))}
      </div>
    </div>
  );
};
```

#### Обновленная лента

**FeedScreen.tsx** (обновления)
```typescript
const FeedScreen: React.FC = () => {
  const [catches, setCatches] = useState<CatchRecord[]>([]);
  const [loading, setLoading] = useState(false);
  const [activeFilter, setActiveFilter] = useState<'all' | 'following' | 'nearby'>('all');
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [userLocation, setUserLocation] = useState<{ latitude: number; longitude: number } | null>(null);

  useEffect(() => {
    loadFeed();
    getUserLocation();
  }, [activeFilter]);

  const getUserLocation = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setUserLocation({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
          });
        },
        (error) => {
          console.warn('Геолокация недоступна:', error);
        }
      );
    }
  };

  const loadFeed = async (page: number = 1, append: boolean = false) => {
    try {
      setLoading(true);
      
      let response;
      const params = { limit: 20, page };
      
      switch (activeFilter) {
        case 'following':
          response = await getFollowingFeed(20, page);
          break;
        case 'nearby':
          if (userLocation) {
            response = await getNearbyFeed(
              userLocation.latitude,
              userLocation.longitude,
              50,
              20,
              page
            );
          } else {
            response = await getFeed({ ...params, type: 'all' });
          }
          break;
        default:
          response = await getFeed({ ...params, type: 'all' });
      }
      
      const newCatches = response.data.data;
      
      if (append) {
        setCatches(prev => [...prev, ...newCatches]);
      } else {
        setCatches(newCatches);
      }
      
      setHasMore(page < response.data.last_page);
      setCurrentPage(page);
    } catch (error) {
      console.error('Ошибка загрузки ленты:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (filter: 'all' | 'following' | 'nearby') => {
    setActiveFilter(filter);
    setCurrentPage(1);
    setHasMore(true);
  };

  const loadMore = () => {
    if (!loading && hasMore) {
      loadFeed(currentPage + 1, true);
    }
  };

  return (
    <div className="screen">
      <FeedFilters
        activeFilter={activeFilter}
        onFilterChange={handleFilterChange}
        className="feed-filters"
      />
      
      <div className="feed">
        {catches.map((catchRecord) => (
          <div key={catchRecord.id} className="catch-card glass">
            <div className="catch-header">
              <div className="user-info">
                <Avatar 
                  src={catchRecord.user.avatar} 
                  size={40}
                  crownIconUrl={catchRecord.user.crown_icon_url}
                  isPremium={catchRecord.user.is_premium}
                />
                <div className="user-details">
                  <div className="user-name-row">
                    <span className="user-name">{catchRecord.user.name}</span>
                    <OnlineIndicator
                      isOnline={catchRecord.user.is_online || false}
                      lastSeenAt={catchRecord.user.last_seen_at}
                      size="small"
                    />
                  </div>
                  <div className="user-stats">
                    {catchRecord.user.followers_count} подписчиков
                  </div>
                </div>
              </div>
            </div>
            
            {/* Остальной контент улова */}
          </div>
        ))}
        
        {hasMore && (
          <div className="load-more-section">
            <button
              className="load-more-button"
              onClick={loadMore}
              disabled={loading}
            >
              {loading ? 'Загрузка...' : 'Загрузить еще'}
            </button>
          </div>
        )}
      </div>
    </div>
  );
};
```

## API Endpoints

### Подписки
- `POST /api/follow` - Подписаться на пользователя
- `POST /api/unfollow` - Отписаться от пользователя  
- `POST /api/follow/toggle` - Переключить подписку
- `GET /api/users/{user}/followers` - Получить подписчиков
- `GET /api/users/{user}/following` - Получить подписки
- `GET /api/follow/suggestions` - Предложения подписок
- `GET /api/follow/mutual/{user}` - Общие подписки

### Онлайн-статус
- `POST /api/online/update` - Обновить статус
- `POST /api/online/offline` - Установить офлайн
- `GET /api/online/status` - Получить статус
- `GET /api/online/users` - Онлайн пользователи
- `GET /api/online/recently-active` - Недавно активные
- `GET /api/online/count` - Количество онлайн

### Лента
- `GET /api/feed` - Общая лента (с параметрами type, latitude, longitude, radius)
- `GET /api/feed/personal` - Персональная лента
- `GET /api/feed/nearby` - Локальная лента
- `GET /api/feed/following` - Лента подписок

## Конфигурация

### Ограничения медиа по ролям
```php
// config/media.php
'limits' => [
    'user' => [
        'max_photos' => 10,
        'max_videos' => 0,
        'max_media_total' => 10,
        'video_enabled' => false,
    ],
    'pro' => [
        'max_photos' => 20,
        'max_videos' => 1,
        'max_media_total' => 21,
        'video_enabled' => true,
    ],
    'premium' => [
        'max_photos' => 20,
        'max_videos' => 1,
        'max_media_total' => 21,
        'video_enabled' => true,
    ],
]
```

## Статистика реализации

### Backend
- ✅ **Миграции**: 3 новые миграции для подписок, статистики и медиа
- ✅ **Модели**: Обновлены User и CatchRecord, добавлена Follow
- ✅ **Контроллеры**: FollowController, OnlineStatusController, обновлен FeedController
- ✅ **API**: 15+ новых endpoints для подписок и онлайн-статуса
- ✅ **Конфигурация**: Настройки ограничений медиа по ролям

### Frontend  
- ✅ **Типы**: Обновлены интерфейсы User, CatchRecord, добавлены новые типы
- ✅ **API**: 15+ новых функций для работы с подписками и онлайн-статусом
- ✅ **Компоненты**: FollowStats, FollowersModal, OnlineIndicator, FeedFilters
- ✅ **Страницы**: Обновлены ProfilePage и FeedScreen
- ✅ **Стили**: Полный набор CSS для всех новых компонентов

### Функциональность
- ✅ **Система подписок**: Подписка/отписка, подсчет статистики
- ✅ **Фильтрация ленты**: Общая, подписки, локальная
- ✅ **Медиа-поддержка**: Фото/видео с ограничениями по ролям
- ✅ **Онлайн-статус**: Индикаторы, пульсирующие метки
- ✅ **Модальные окна**: Списки подписчиков/подписок с поиском
- ✅ **Адаптивность**: Полная поддержка мобильных устройств

## Следующие шаги

1. **Уведомления**: Push-уведомления о новых подписках и уловах
2. **Аналитика**: Детальная статистика активности пользователей
3. **Рекомендации**: Умные предложения подписок на основе интересов
4. **Группы**: Создание и управление группами рыбаков
5. **События**: Организация совместных рыбалок
6. **Чат**: Личные сообщения между пользователями
7. **Стримы**: Прямые трансляции рыбалки в реальном времени

Система подписок полностью интегрирована и готова к использованию!
