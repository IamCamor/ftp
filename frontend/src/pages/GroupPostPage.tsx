import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import PageLayout from '../components/PageLayout';
import Avatar from '../components/Avatar';
import Icon from '../components/Icon';
import { getGroupPost } from '../api';
import type { GroupPost } from '../types';

const GroupPostPage: React.FC = () => {
  const { groupId, postId } = useParams<{ groupId: string; postId: string }>();
  const navigate = useNavigate();
  const [post, setPost] = useState<GroupPost | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (groupId && postId) {
      loadPost();
    }
  }, [groupId, postId]);

  const loadPost = async () => {
    try {
      setLoading(true);
      setError(null);
      const postData = await getGroupPost(parseInt(groupId!), parseInt(postId!));
      setPost(postData);
    } catch (err) {
      console.error('Failed to load group post:', err);
      setError('Не удалось загрузить пост');
    } finally {
      setLoading(false);
    }
  };

  const handleBack = () => {
    navigate(`/groups/${groupId}`);
  };

  if (loading) {
    return (
      <PageLayout
        title="Загрузка поста"
        description="Загружаем пост из группы"
        className="screen"
      >
        <div className="page-loading">
          <div className="loading-spinner"></div>
          <p>Загружаем пост...</p>
        </div>
      </PageLayout>
    );
  }

  if (error || !post) {
    return (
      <PageLayout
        title="Ошибка"
        description="Не удалось загрузить пост"
        className="screen"
      >
        <div className="page-error">
          <div className="error-content">
            <Icon name="error" size="xl" />
            <h2>Ошибка загрузки</h2>
            <p>{error || 'Пост не найден'}</p>
            <button className="btn btn-primary" onClick={handleBack}>
              Вернуться в группу
            </button>
          </div>
        </div>
      </PageLayout>
    );
  }

  return (
    <PageLayout
      title={post.title}
      description={post.content?.substring(0, 160) || 'Пост из группы'}
      className="screen"
    >
      <div className="page-header">
        <button className="back-button" onClick={handleBack}>
          <Icon name="arrow_back" size="md" />
        </button>
        <h1>Пост из группы</h1>
      </div>

      <div className="page-content">
        <article className="group-post-detail">
          <header className="post-header">
            <div className="post-meta">
              <div className="post-author">
                <Avatar 
                  src={post.author.photo_url} 
                  size="xl"
                  crownIconUrl={post.author.crown_icon_url}
                  isPremium={post.author.is_premium}
                  name={post.author.name}
                />
                <div className="author-info">
                  <h3 className="author-name">{post.author.name}</h3>
                  <span className="post-date">
                    {new Date(post.created_at).toLocaleDateString('ru-RU', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </span>
                </div>
              </div>
              
              <div className="post-actions">
                <button className="action-button" title="Поделиться">
                  <Icon name="share" size="md" />
                </button>
                <button className="action-button" title="Сохранить">
                  <Icon name="bookmark" size="md" />
                </button>
              </div>
            </div>
          </header>

          <div className="post-content">
            <h2 className="post-title">{post.title}</h2>
            
            {post.content && (
              <div className="post-text">
                {post.content.split('\n').map((paragraph, index) => (
                  <p key={index}>{paragraph}</p>
                ))}
              </div>
            )}

            {post.images && post.images.length > 0 && (
              <div className="post-images">
                {post.images.map((image, index) => (
                  <div key={index} className="post-image">
                    <img 
                      src={image.url} 
                      alt={`Изображение ${index + 1}`}
                      loading="lazy"
                    />
                  </div>
                ))}
              </div>
            )}

            {post.location && (
              <div className="post-location">
                <Icon name="place" size="md" />
                <span>{post.location}</span>
              </div>
            )}
          </div>

          <footer className="post-footer">
            <div className="post-stats">
              <div className="stat-item">
                <Icon name="favorite" size="sm" />
                <span>{post.likes_count || 0}</span>
              </div>
              <div className="stat-item">
                <Icon name="comment" size="sm" />
                <span>{post.comments_count || 0}</span>
              </div>
              <div className="stat-item">
                <Icon name="visibility" size="sm" />
                <span>{post.views_count || 0}</span>
              </div>
            </div>

            <div className="post-actions-main">
              <button className="action-btn like-btn">
                <Icon name="favorite_border" size="md" />
                <span>Нравится</span>
              </button>
              <button className="action-btn comment-btn">
                <Icon name="comment" size="md" />
                <span>Комментировать</span>
              </button>
              <button className="action-btn share-btn">
                <Icon name="share" size="md" />
                <span>Поделиться</span>
              </button>
            </div>
          </footer>
        </article>

        {/* Комментарии */}
        <section className="comments-section">
          <h3>Комментарии</h3>
          <div className="comments-list">
            <div className="no-comments">
              <Icon name="comment" size="md" />
              <p>Пока нет комментариев</p>
              <button className="btn btn-secondary">
                Оставить первый комментарий
              </button>
            </div>
          </div>
        </section>
      </div>
    </PageLayout>
  );
};

export default GroupPostPage;

