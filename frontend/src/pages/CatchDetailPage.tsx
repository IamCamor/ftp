import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Heart, MessageCircle, Share, MoreHorizontal, MapPin, Fish, Scale, Clock } from 'lucide-react';
import { likeCatch, getCatchComments } from '../api';
import type { CatchRecord, CatchComment } from '../types';
import { validateCatchId } from '../utils/catchValidator';
import { formatDistanceToNow } from 'date-fns';
import { ru } from 'date-fns/locale';
import Avatar from '../components/Avatar';
import PhotoCollage from '../components/PhotoCollage';
import { mockCatches } from '../data/mockData';
import '../styles/catch-feed.css';

const CatchDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [catchRecord, setCatchRecord] = useState<CatchRecord | null>(null);
  const [comments, setComments] = useState<CatchComment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (id) {
      // Сначала валидируем ID
      const validation = validateCatchId(id);
      if (!validation.isValid) {
        setError(validation.error || 'Неверный ID улова');
        if (validation.suggestion) {
          // Автоматически перенаправляем на предложенный ID
          navigate(`/catch/${validation.suggestion}`, { replace: true });
          return;
        }
        setLoading(false);
        return;
      }
      
      loadCatchDetail();
      loadComments();
    }
  }, [id, navigate]);


  const loadCatchDetail = async () => {
    try {
      setLoading(true);
      setError(null);
      // Используем тестовые данные
      const catchId = Number(id);
      const mockCatch = mockCatches.find(c => c.id === catchId);
      
      if (mockCatch) {
        setCatchRecord(mockCatch);
      } else {
        // Если улов не найден, показываем первый доступный улов
        if (mockCatches.length > 0) {
          setCatchRecord(mockCatches[0]);
        } else {
          setError('Улов не найден');
        }
      }
    } catch (err: any) {
      setError('Не удалось загрузить улов');
      console.error('Catch detail loading error:', err);
    } finally {
      setLoading(false);
    }
  };

  const loadComments = async () => {
    try {
      const response = await getCatchComments(Number(id));
      setComments(response.data);
    } catch (err: any) {
      // Не показываем ошибку для комментариев, если улов не найден
      if (err.message?.includes('Authentication required')) {
        console.warn('Comments require authentication, skipping...');
      } else {
        console.error('Comments loading error:', err);
      }
    }
  };

  const handleLike = async (id: number) => {
    try {
      await likeCatch(id);
    } catch (error) {
      console.error('Failed to like catch:', error);
    }
  };





  if (loading) {
    return (
      <div className="screen">
        <div className="loading">Загрузка улова...</div>
      </div>
    );
  }

  if (error || !catchRecord) {
    return (
      <div className="screen">
        <div className="error">
          <div className="error-content">
            <h2>Улов не найден</h2>
            <p>Улов с ID {id} не существует в базе данных.</p>
            
            
            <div className="error-actions">
              <button onClick={() => navigate('/feed')} className="btn btn-secondary">
                Перейти к ленте
              </button>
              <button onClick={() => navigate(-1)} className="btn btn-outline">
                Назад
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  const formatWeight = (weight: number | undefined) => {
    if (!weight) return null;
    return weight >= 1000 ? `${(weight / 1000).toFixed(1)} кг` : `${weight} г`;
  };

  const formatLength = (length: number | undefined) => {
    if (!length) return null;
    return `${length} см`;
  };

  // Собираем все фотографии для коллажа
  const allPhotos = [];
  if (catchRecord.photo_url) {
    allPhotos.push(catchRecord.photo_url);
  }
  if (catchRecord.additional_photos) {
    try {
      const additionalPhotos = JSON.parse(catchRecord.additional_photos);
      if (Array.isArray(additionalPhotos)) {
        allPhotos.push(...additionalPhotos);
      }
    } catch (e) {
      console.error('Error parsing additional photos:', e);
    }
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 p-4 flex items-center">
        <button 
          onClick={() => navigate(-1)}
          className="mr-4 p-2 hover:bg-gray-100 rounded-full transition-colors"
        >
          <ArrowLeft size={24} />
        </button>
        <h1 className="text-xl font-bold text-gray-900">Улов #{catchRecord.id}</h1>
      </div>

      {/* Catch Details */}
      <div className="max-w-2xl mx-auto bg-white shadow-sm">
        {/* User Info */}
        <div className="p-4 border-b border-gray-100">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <Avatar 
                src={catchRecord.user.photo_url} 
                name={catchRecord.user.name}
                size="lg"
              />
              <div>
                <h3 className="font-semibold text-gray-900">{catchRecord.user.name}</h3>
                <p className="text-sm text-gray-500">
                  {formatDistanceToNow(new Date(catchRecord.created_at), { 
                    addSuffix: true, 
                    locale: ru 
                  })}
                </p>
              </div>
            </div>
            <button className="p-2 hover:bg-gray-100 rounded-full transition-colors">
              <MoreHorizontal size={20} />
            </button>
          </div>
        </div>

        {/* Photos */}
        {allPhotos.length > 0 && (
          <div className="relative">
            <PhotoCollage photos={allPhotos} />
          </div>
        )}

        {/* Catch Info */}
        <div className="p-4 space-y-4">
          {/* Actions */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <button 
                onClick={() => handleLike(catchRecord.id)}
                className={`flex items-center space-x-2 transition-all duration-200 ${
                  catchRecord.liked_by_me ? 'text-red-500 like-animation' : 'text-gray-600 hover:text-red-500'
                }`}
              >
                <Heart 
                  size={24} 
                  fill={catchRecord.liked_by_me ? 'currentColor' : 'none'} 
                />
                <span className="font-medium">{catchRecord.likes_count}</span>
              </button>
              <button className="flex items-center space-x-2 text-gray-600 hover:text-blue-500 transition-colors">
                <MessageCircle size={24} />
                <span className="font-medium">{catchRecord.comments_count}</span>
              </button>
              <button className="text-gray-600 hover:text-green-500 transition-colors">
                <Share size={24} />
              </button>
            </div>
          </div>

          {/* Catch Details */}
          <div className="grid grid-cols-2 gap-4">
            {catchRecord.fish_species && (
              <div className="flex items-center space-x-2 p-3 bg-blue-50 rounded-lg">
                <Fish size={20} className="text-blue-600" />
                <div>
                  <p className="text-sm text-gray-500">Вид рыбы</p>
                  <p className="font-semibold text-gray-900">{catchRecord.fish_species.name}</p>
                </div>
              </div>
            )}
            
            {formatWeight(catchRecord.weight) && (
              <div className="flex items-center space-x-2 p-3 bg-green-50 rounded-lg">
                <Scale size={20} className="text-green-600" />
                <div>
                  <p className="text-sm text-gray-500">Вес</p>
                  <p className="font-semibold text-gray-900">{formatWeight(catchRecord.weight)}</p>
                </div>
              </div>
            )}
            
            {formatLength(catchRecord.length) && (
              <div className="flex items-center space-x-2 p-3 bg-purple-50 rounded-lg">
                <Clock size={20} className="text-purple-600" />
                <div>
                  <p className="text-sm text-gray-500">Длина</p>
                  <p className="font-semibold text-gray-900">{formatLength(catchRecord.length)}</p>
                </div>
              </div>
            )}
            
            {catchRecord.point && (
              <div className="flex items-center space-x-2 p-3 bg-orange-50 rounded-lg">
                <MapPin size={20} className="text-orange-600" />
                <div>
                  <p className="text-sm text-gray-500">Место</p>
                  <p className="font-semibold text-gray-900">{catchRecord.point.name}</p>
                </div>
              </div>
            )}
          </div>

          {/* Description */}
          {catchRecord.notes && (
            <div className="bg-gray-50 p-4 rounded-lg">
              <p className="text-gray-900">{catchRecord.notes}</p>
            </div>
          )}

          {/* Comments */}
          {comments.length > 0 && (
            <div className="space-y-3">
              <h4 className="font-semibold text-gray-900">Комментарии</h4>
              {comments.map(comment => (
                <div key={comment.id} className="flex space-x-3">
                  <Avatar 
                    src={comment.user.photo_url} 
                    name={comment.user.name}
                    size="sm"
                  />
                  <div className="flex-1">
                    <div className="bg-gray-100 p-3 rounded-lg">
                      <p className="font-medium text-gray-900">{comment.user.name}</p>
                      <p className="text-gray-700">{comment.body}</p>
                    </div>
                    <p className="text-xs text-gray-500 mt-1">
                      {formatDistanceToNow(new Date(comment.created_at), { 
                        addSuffix: true, 
                        locale: ru 
                      })}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default CatchDetailPage;