import React, { useState, useEffect } from 'react';
import { getSubscriptionStatus } from '../api';
import type { SubscriptionStatus } from '../types';
import Icon from './Icon';

interface SubscriptionStatusProps {
  userId?: number;
  className?: string;
}

const SubscriptionStatusComponent: React.FC<SubscriptionStatusProps> = ({ 
  userId, 
  className = '' 
}) => {
  const [status, setStatus] = useState<SubscriptionStatus | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStatus();
  }, [userId]);

  const loadStatus = async () => {
    try {
      const data = await getSubscriptionStatus();
      setStatus(data);
    } catch (error) {
      console.error('Error loading subscription status:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className={`subscription-status loading ${className}`}>
        <Icon name="hourglass_empty" />
        <span>Загрузка...</span>
      </div>
    );
  }

  if (!status) {
    return null;
  }

  const getStatusText = () => {
    if (status.is_premium) {
      return 'Premium';
    } else if (status.is_pro) {
      return 'Pro';
    } else {
      return 'Обычный';
    }
  };

  const getStatusClass = () => {
    if (status.is_premium) {
      return 'premium';
    } else if (status.is_pro) {
      return 'pro';
    } else {
      return 'user';
    }
  };

  const getActiveSubscription = () => {
    return status.active_subscriptions.find(sub => sub.status === 'active');
  };

  const activeSubscription = getActiveSubscription();

  return (
    <div className={`subscription-status ${getStatusClass()} ${className}`}>
      <div className="status-badge">
        {status.is_premium && status.crown_icon_url && (
          <img 
            src={status.crown_icon_url} 
            alt="Crown" 
            className="crown-icon" 
          />
        )}
        <span className="status-text">{getStatusText()}</span>
      </div>
      
      {activeSubscription && (
        <div className="subscription-details">
          <div className="subscription-type">
            {activeSubscription.type === 'premium' ? 'Premium' : 'Pro'}
          </div>
          <div className="subscription-expires">
            {activeSubscription.expires_at && (
              <>
                <Icon name="schedule" />
                <span>
                  до {new Date(activeSubscription.expires_at).toLocaleDateString('ru-RU')}
                </span>
              </>
            )}
          </div>
        </div>
      )}
      
      <div className="bonus-balance">
        <Icon name="star" />
        <span>{status.bonus_balance}</span>
      </div>
    </div>
  );
};

export default SubscriptionStatusComponent;
