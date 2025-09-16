import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import TextInput from '../../components/Form/Text';
import { register, oauthRedirect } from '../../api';
import config from '../../config';

const RegisterPage: React.FC = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    name: '',
    username: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await register(formData);
      if (response.token) {
        localStorage.setItem('token', response.token);
        navigate(config.routes.feed);
      } else {
        setError('Ошибка получения токена');
      }
    } catch (err: any) {
      setError(err.message || 'Ошибка регистрации');
    } finally {
      setLoading(false);
    }
  };

  const handleOAuthLogin = (provider: string) => {
    window.location.href = oauthRedirect(provider);
  };

  // Упрощенная валидация - убираем согласия пока что

  return (
    <div className="screen auth-screen">
      <div className="auth-container glass">
        <div className="auth-header">
          <img src={config.logoUrl} alt="FishTrackPro" className="auth-logo" />
          <h1>Регистрация в FishTrackPro</h1>
          <p>Присоединяйтесь к сообществу рыбаков!</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-form">
          {error && <div className="error-message">{error}</div>}

          <TextInput
            label="Имя"
            value={formData.name}
            onChange={(value) => setFormData(prev => ({ ...prev, name: value }))}
            placeholder="Введите ваше имя"
            required
            icon="person"
          />

          <TextInput
            label="Email"
            type="email"
            value={formData.email}
            onChange={(value) => setFormData(prev => ({ ...prev, email: value }))}
            placeholder="Введите ваш email"
            required
            icon="email"
          />

          <TextInput
            label="Имя пользователя"
            value={formData.username}
            onChange={(value) => setFormData(prev => ({ ...prev, username: value }))}
            placeholder="Выберите имя пользователя"
            icon="alternate_email"
          />

          <TextInput
            label="Пароль"
            type="password"
            value={formData.password}
            onChange={(value) => setFormData(prev => ({ ...prev, password: value }))}
            placeholder="Минимум 8 символов"
            required
            icon="lock"
          />


          <button
            type="submit"
            className="btn btn-primary btn-full"
            disabled={loading}
          >
            {loading ? 'Регистрация...' : 'Зарегистрироваться'}
          </button>
        </form>

        <div className="auth-divider">
          <span>или</span>
        </div>

        <div className="oauth-buttons">
          {config.features.auth.providers.google && (
            <button
              className="oauth-button google"
              onClick={() => handleOAuthLogin('google')}
            >
              <span className="oauth-icon">G</span>
              Войти через Google
            </button>
          )}

          {config.features.auth.providers.vk && (
            <button
              className="oauth-button vk"
              onClick={() => handleOAuthLogin('vk')}
            >
              <span className="oauth-icon">VK</span>
              Войти через VK
            </button>
          )}

          {config.features.auth.providers.yandex && (
            <button
              className="oauth-button yandex"
              onClick={() => handleOAuthLogin('yandex')}
            >
              <span className="oauth-icon">Я</span>
              Войти через Яндекс
            </button>
          )}

          {config.features.auth.providers.apple && (
            <button
              className="oauth-button apple"
              onClick={() => handleOAuthLogin('apple')}
            >
              <span className="oauth-icon">🍎</span>
              Войти через Apple
            </button>
          )}
        </div>

        <div className="auth-footer">
          <p>
            Уже есть аккаунт?{' '}
            <Link to={config.routes.auth.login} className="auth-link">
              Войти
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;

