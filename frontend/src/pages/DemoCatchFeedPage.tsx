import React from 'react';
import CatchFeed from '../components/CatchFeed';

const DemoCatchFeedPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Заголовок демо */}
      <div className="bg-white border-b border-gray-200 p-4 text-center">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">
          🎣 Демо: Лента уловов
        </h1>
        <p className="text-gray-600">
          Современная лента в стиле Instagram для рыболовного приложения
        </p>
      </div>
      
      {/* Основной компонент ленты */}
      <CatchFeed activeTab="all" />
    </div>
  );
};

export default DemoCatchFeedPage;
