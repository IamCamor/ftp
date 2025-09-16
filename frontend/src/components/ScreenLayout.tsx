import React from 'react';
import PageLayout from './PageLayout';

interface ScreenLayoutProps {
  children: React.ReactNode;
  title?: string;
  description?: string;
  keywords?: string[];
  type?: 'website' | 'article';
  image?: string;
  loading?: boolean;
  error?: string | null;
  onRetry?: () => void;
}

const ScreenLayout: React.FC<ScreenLayoutProps> = ({
  children,
  title,
  description,
  keywords = [],
  type = 'website',
  image,
  loading = false,
  error = null,
  onRetry
}) => {
  return (
    <PageLayout
      title={title}
      description={description}
      keywords={keywords}
      type={type}
      image={image}
      className="screen"
      loading={loading}
      error={error}
      onRetry={onRetry}
    >
      {children}
    </PageLayout>
  );
};

export default ScreenLayout;





