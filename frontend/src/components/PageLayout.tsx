import React from 'react';
import SEOHead from './SEOHead';

interface PageLayoutProps {
  children: React.ReactNode;
  title?: string;
  description?: string;
  keywords?: string[];
  type?: 'website' | 'article';
  image?: string;
  className?: string;
  loading?: boolean;
  error?: string | null;
  onRetry?: () => void;
}

const PageLayout: React.FC<PageLayoutProps> = ({
  children,
  title,
  description,
  keywords = [],
  type = 'website',
  image,
  className = '',
  loading = false,
  error = null,
  onRetry
}) => {
  // Если есть SEO данные, добавляем SEOHead
  const seoProps = title ? {
    title,
    description,
    keywords,
    type,
    image
  } : null;

  // Состояние загрузки
  if (loading) {
    return (
      <div className={`page ${className}`}>
        {seoProps && <SEOHead {...seoProps} />}
        <div className="page-loading">
          <div className="loading-spinner">
            <div className="spinner"></div>
            <p>Загрузка...</p>
          </div>
        </div>
      </div>
    );
  }

  // Состояние ошибки
  if (error) {
    return (
      <div className={`page ${className}`}>
        {seoProps && <SEOHead {...seoProps} />}
        <div className="page-error">
          <div className="error-content">
            <div className="error-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
            </div>
            <h3>Произошла ошибка</h3>
            <p>{error}</p>
            {onRetry && (
              <button onClick={onRetry} className="btn btn-primary">
                Попробовать снова
              </button>
            )}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className={`page ${className}`}>
      {seoProps && <SEOHead {...seoProps} />}
      <div className="page-content">
        {children}
      </div>
    </div>
  );
};

export default PageLayout;
