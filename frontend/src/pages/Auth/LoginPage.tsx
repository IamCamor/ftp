import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import TextInput from '../../components/Form/Text';
import { login, oauthRedirect } from '../../api';
import config from '../../config';

const LoginPage: React.FC = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await login(formData);
      localStorage.setItem('token', response.token);
      navigate(config.routes.feed);
    } catch (err: any) {
      setError(err.message || 'Ошибка входа');
    } finally {
      setLoading(false);
    }
  };

  const handleOAuthLogin = (provider: string) => {
    window.location.href = oauthRedirect(provider);
  };

  return (
    <div className="screen auth-screen">
      <div className="auth-container glass">
        <div className="auth-header">
          <h1>Вход в FishTrackPro</h1>
          <p>Добро пожаловать обратно!</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-form">
          {error && <div className="error-message">{error}</div>}

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
            label="Пароль"
            type="password"
            value={formData.password}
            onChange={(value) => setFormData(prev => ({ ...prev, password: value }))}
            placeholder="Введите пароль"
            required
            icon="lock"
          />

          <button
            type="submit"
            className="btn btn-primary btn-full"
            disabled={loading}
          >
            {loading ? 'Вход...' : 'Войти'}
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
            Нет аккаунта?{' '}
            <Link to={config.routes.auth.register} className="auth-link">
              Зарегистрироваться
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;

