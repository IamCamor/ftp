import React, { useState, useEffect, useRef, useCallback } from 'react';
import { 
  Heart, 
  MessageCircle, 
  Share2, 
  MapPin, 
  Calendar, 
  Fish,
  Users,
  Calendar as CalendarIcon,
  UserPlus,
  Cloud
} from 'lucide-react';
import './CatchFeed.css';
import { mockCatches } from '../data/mockData';

// Типы данных
interface CatchPost {
  id: string;
  user: {
    id: string;
    name: string;
    avatar: string;
  };
  photo: string;
  location: string;
  date: string;
  fishType: string;
  weight: number;
  likes: number;
  comments: number;
  isLiked: boolean;
}

interface Group {
  id: string;
  name: string;
  photo: string;
  members: number;
}

interface Event {
  id: string;
  title: string;
  date: string;
  location: string;
  photo: string;
}

interface WeatherPoint {
  id: string;
  name: string;
  temperature: number;
  condition: string;
  icon: string;
}

// Компонент поста улова
const CatchPostCard: React.FC<{ post: CatchPost; onLike: (id: string) => void }> = ({ post, onLike }) => {
  const [isLiked, setIsLiked] = useState(post.isLiked);
  const [likes, setLikes] = useState(post.likes);

  const handleLike = () => {
    const newLiked = !isLiked;
    setIsLiked(newLiked);
    setLikes(prev => newLiked ? prev + 1 : prev - 1);
    onLike(post.id);
  };

  return (
    <div className="catch-post bg-white rounded-lg shadow-sm border border-gray-100 mb-4 overflow-hidden">
      {/* Заголовок поста */}
      <div className="flex items-center p-4 pb-3">
        <div className="w-10 h-10 rounded-full overflow-hidden mr-3">
          <img 
            src={post.user.avatar} 
            alt={post.user.name}
            className="w-full h-full object-cover"
          />
        </div>
        <div className="flex-1">
          <h3 className="font-semibold text-gray-900">{post.user.name}</h3>
          <div className="flex items-center text-sm text-gray-500">
            <MapPin className="w-3 h-3 mr-1 icon-bounce" />
            <span>{post.location}</span>
          </div>
        </div>
        <div className="text-sm text-gray-500">
          <Calendar className="w-4 h-4 inline mr-1 icon-bounce" />
          {post.date}
        </div>
      </div>

      {/* Фото улова */}
      <div className="relative">
        <img 
          src={post.photo} 
          alt="Улов"
          className="w-full h-80 object-cover"
        />
        {/* Информация о рыбе поверх фото */}
        <div className="absolute top-4 left-4 bg-black bg-opacity-50 text-white px-3 py-2 rounded-lg">
          <div className="flex items-center">
            <Fish className="w-4 h-4 mr-2 icon-bounce" />
            <span className="font-medium">{post.fishType}</span>
          </div>
        </div>
        {/* Вес улова */}
        <div className="weight-badge absolute bottom-4 right-4 text-white px-4 py-2 rounded-lg">
          <span className="text-2xl font-bold">{post.weight}</span>
          <span className="text-sm ml-1">кг</span>
        </div>
      </div>

      {/* Действия */}
      <div className="p-4">
        <div className="flex items-center justify-between mb-3">
          <div className="flex items-center space-x-4">
            <button 
              onClick={handleLike}
              className={`flex items-center space-x-2 transition-all duration-200 ${
                isLiked ? 'text-red-500 like-animation' : 'text-gray-600 hover:text-red-500'
              }`}
            >
              <Heart className={`w-6 h-6 icon-bounce ${isLiked ? 'fill-current' : ''}`} />
              <span className="font-medium">{likes}</span>
            </button>
            <button className="flex items-center space-x-2 text-gray-600 hover:text-blue-500 transition-colors">
              <MessageCircle className="w-6 h-6 icon-bounce" />
              <span className="font-medium">{post.comments}</span>
            </button>
            <button className="text-gray-600 hover:text-green-500 transition-colors">
              <Share2 className="w-6 h-6 icon-bounce" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

// Компонент блока групп
const GroupsSlider: React.FC<{ groups: Group[] }> = ({ groups }) => (
  <div className="special-block bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
    <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <Users className="w-5 h-5 mr-2 text-blue-500 icon-bounce" />
      Популярные группы
    </h3>
    <div className="flex space-x-4 overflow-x-auto pb-2 custom-scrollbar">
      {groups.map(group => (
        <div key={group.id} className="group-item flex-shrink-0 text-center">
          <div className="w-16 h-16 rounded-full overflow-hidden mb-2">
            <img 
              src={group.photo} 
              alt={group.name}
              className="w-full h-full object-cover"
            />
          </div>
          <p className="text-sm font-medium text-gray-900">{group.name}</p>
          <p className="text-xs text-gray-500">{group.members} участников</p>
        </div>
      ))}
    </div>
  </div>
);

// Компонент блока мероприятий
const EventsBlock: React.FC<{ events: Event[] }> = ({ events }) => (
  <div className="special-block bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
    <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <CalendarIcon className="w-5 h-5 mr-2 text-green-500 icon-bounce" />
      Ближайшие мероприятия
    </h3>
    <div className="space-y-3">
      {events.map(event => (
        <div key={event.id} className="flex items-center space-x-3 group-item">
          <div className="w-12 h-12 rounded-lg overflow-hidden">
            <img 
              src={event.photo} 
              alt={event.title}
              className="w-full h-full object-cover"
            />
          </div>
          <div className="flex-1">
            <h4 className="font-medium text-gray-900">{event.title}</h4>
            <p className="text-sm text-gray-500">{event.date} • {event.location}</p>
          </div>
        </div>
      ))}
    </div>
  </div>
);

// Компонент приглашения друзей
const InviteFriendsBlock: React.FC = () => (
  <div className="special-block invite-gradient rounded-lg p-6 mb-4 text-white">
    <div className="flex items-center mb-4">
      <UserPlus className="w-6 h-6 mr-3 icon-bounce" />
      <h3 className="text-lg font-semibold">Пригласите друзей</h3>
    </div>
    <p className="text-blue-100 mb-4">
      Поделитесь приложением с друзьями и получайте бонусы за каждого приглашенного!
    </p>
    <button className="btn-primary bg-white text-blue-600 px-6 py-2 rounded-lg font-medium">
      Отправить приглашения
    </button>
  </div>
);

// Компонент блока погоды
const WeatherBlock: React.FC<{ weatherPoints: WeatherPoint[] }> = ({ weatherPoints }) => (
  <div className="special-block bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
    <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <Cloud className="w-5 h-5 mr-2 text-cyan-500 icon-bounce" />
      Погода в ваших точках
    </h3>
    <div className="grid grid-cols-2 gap-3">
      {weatherPoints.map(point => (
        <div key={point.id} className="weather-item flex items-center space-x-3 p-3 rounded-lg">
          <div className="text-2xl">{point.icon}</div>
          <div className="flex-1">
            <p className="font-medium text-gray-900">{point.name}</p>
            <p className="text-sm text-gray-500">{point.condition}</p>
          </div>
          <div className="text-lg font-bold text-blue-600">{point.temperature}°</div>
        </div>
      ))}
    </div>
  </div>
);

// Основной компонент ленты
interface CatchFeedProps {
  activeTab: 'all' | 'local' | 'friends';
}

const CatchFeed: React.FC<CatchFeedProps> = ({ activeTab }) => {
  const [posts, setPosts] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [page, setPage] = useState(1);
  const observerRef = useRef<IntersectionObserver | null>(null);
  const loadingRef = useRef<HTMLDivElement | null>(null);

  // Моковые данные

  const mockGroups: Group[] = [
    { id: '1', name: 'Щукари', photo: 'https://picsum.photos/100/100?random=100', members: 1250 },
    { id: '2', name: 'Карпятники', photo: 'https://picsum.photos/100/100?random=101', members: 890 },
    { id: '3', name: 'Спиннингисты', photo: 'https://picsum.photos/100/100?random=102', members: 2100 },
    { id: '4', name: 'Зимняя рыбалка', photo: 'https://picsum.photos/100/100?random=103', members: 750 }
  ];

  const mockEvents: Event[] = [
    { id: '1', title: 'Турнир по ловле щуки', date: '15.01.2025', location: 'Москва', photo: 'https://picsum.photos/100/100?random=200' },
    { id: '2', title: 'Семинар по карпфишингу', date: '20.01.2025', location: 'СПб', photo: 'https://picsum.photos/100/100?random=201' }
  ];

  const mockWeatherPoints: WeatherPoint[] = [
    { id: '1', name: 'Озеро Светлое', temperature: -5, condition: 'Облачно', icon: '☁️' },
    { id: '2', name: 'Река Быстрая', temperature: -8, condition: 'Снег', icon: '❄️' }
  ];

  // Загрузка постов
  const loadPosts = useCallback(async (pageNum: number, tab: string) => {
    setLoading(true);
    try {
      // Используем тестовые данные
      const allData = [...mockCatches];
      
      // Фильтруем по табам
      let filteredData = allData;
      if (tab === 'local') {
        // Фильтр "Локально" - показываем только уловы с координатами
        filteredData = allData.filter(item => item.point?.latitude && item.point?.longitude);
      } else if (tab === 'friends') {
        // Фильтр "Друзья" - показываем только уловы от друзей (пока все)
        filteredData = allData;
      }
      
      // Пагинация
      const startIndex = (pageNum - 1) * 20;
      const endIndex = startIndex + 20;
      const pageData = filteredData.slice(startIndex, endIndex);
      
      if (pageNum === 1) {
        setPosts(pageData);
      } else {
        setPosts(prev => [...prev, ...pageData]);
      }
      
      setHasMore(endIndex < filteredData.length);
    } catch (error) {
      console.error('Feed loading error:', error);
      setPosts([]);
      setHasMore(false);
    } finally {
      setLoading(false);
    }
  }, []);

  // Обработчик лайка
  const handleLike = (postId: string) => {
    // Здесь будет логика отправки лайка на сервер
    console.log('Liked post:', postId);
  };

  // Обработчик смены таба

  // Настройка Intersection Observer для бесконечного скролла
  useEffect(() => {
    observerRef.current = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && hasMore && !loading) {
          const nextPage = page + 1;
          setPage(nextPage);
          loadPosts(nextPage, activeTab);
        }
      },
      { threshold: 0.1 }
    );

    if (loadingRef.current) {
      observerRef.current.observe(loadingRef.current);
    }

    return () => {
      if (observerRef.current) {
        observerRef.current.disconnect();
      }
    };
  }, [hasMore, loading, page, activeTab, loadPosts]);

  // Загрузка начальных данных
  useEffect(() => {
    loadPosts(1, activeTab);
  }, [activeTab, loadPosts]);

  // Функция для вставки дополнительных блоков
  const insertSpecialBlock = (index: number) => {
    const blockType = Math.floor(index / 20);
    
    switch (blockType) {
      case 1:
        return <GroupsSlider key={`groups-${index}`} groups={mockGroups} />;
      case 2:
        return <EventsBlock key={`events-${index}`} events={mockEvents} />;
      case 3:
        return <InviteFriendsBlock key={`invite-${index}`} />;
      case 4:
        return <WeatherBlock key={`weather-${index}`} weatherPoints={mockWeatherPoints} />;
      default:
        return null;
    }
  };

  return (
    <div className="max-w-md mx-auto px-4 pb-20">
      {/* Лента с новым дизайном */}
      <div className="space-y-4">
        {posts.map((post, index) => (
          <React.Fragment key={post.id}>
            <div className="md3-card">
              <CatchPostCard post={post} onLike={handleLike} />
            </div>
            {insertSpecialBlock(index + 1)}
          </React.Fragment>
        ))}

        {/* Индикатор загрузки */}
        {loading && (
          <div className="glass-card p-8 text-center">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
            <p className="md3-body-medium mt-4 text-gray-600">Загрузка...</p>
          </div>
        )}

        {/* Реф для бесконечного скролла */}
        <div ref={loadingRef} className="h-4" />

        {/* Блок окончания ленты */}
        {!hasMore && !loading && (
          <div className="glass-card p-8 text-center">
            <h3 className="md3-title-large mb-2">
              Осмотреть уловы из глобальной ленты
            </h3>
            <p className="md3-body-medium text-gray-600 mb-6">
              Откройте для себя уловы со всего мира
            </p>
            <button className="md3-button md3-button-filled">
              Показать больше
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default CatchFeed;
