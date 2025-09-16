import React, { useState } from 'react';
import CatchFeed from '../components/CatchFeed';

type TabType = 'all' | 'local' | 'friends';

const CatchFeedPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState<TabType>('all');

  const tabs = [
    { id: 'all' as TabType, label: 'Все уловы', icon: '🌍' },
    { id: 'local' as TabType, label: 'Локально', icon: '📍' },
    { id: 'friends' as TabType, label: 'Друзья', icon: '👥' }
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      {/* Header with Glassmorphism */}
      <div className="glass-surface p-6 mx-4 mt-4 mb-6">
        <h1 className="md3-headline-large text-center mb-2">
          🎣 Лента уловов
        </h1>
        <p className="md3-body-large text-center text-gray-600">
          Откройте для себя лучшие уловы рыболовов
        </p>
      </div>
      
      {/* Tab Filter with Material Design 3 */}
      <div className="px-4 mb-6">
        <div className="md3-tabs">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              className={`md3-tab ${activeTab === tab.id ? 'active' : ''}`}
              onClick={() => setActiveTab(tab.id)}
              data-tab={tab.id}
            >
              <span className="mr-2">{tab.icon}</span>
              <span>{tab.label}</span>
            </button>
          ))}
        </div>
      </div>
      
      {/* Main Feed Component */}
      <CatchFeed activeTab={activeTab} />
    </div>
  );
};

export default CatchFeedPage;
