import React, { useState, useEffect } from 'react';
import { getSubscriptionPlans, getSubscriptionStatus, createSubscription } from '../api';
import type { SubscriptionPlansResponse, SubscriptionStatus, SubscriptionPlan, PaymentMethod } from '../types';
import Icon from '../components/Icon';
import Header from '../components/Header';

const SubscriptionPage: React.FC = () => {
  const [plans, setPlans] = useState<SubscriptionPlansResponse | null>(null);
  const [status, setStatus] = useState<SubscriptionStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedPlan, setSelectedPlan] = useState<'pro' | 'premium' | null>(null);
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<string | null>(null);
  const [useTrial, setUseTrial] = useState(false);
  const [processing, setProcessing] = useState(false);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [plansData, statusData] = await Promise.all([
        getSubscriptionPlans(),
        getSubscriptionStatus()
      ]);
      setPlans(plansData);
      setStatus(statusData);
    } catch (error) {
      console.error('Error loading subscription data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubscribe = async () => {
    if (!selectedPlan || !selectedPaymentMethod) return;

    setProcessing(true);
    try {
      await createSubscription({
        type: selectedPlan,
        payment_method: selectedPaymentMethod as any,
        use_trial: useTrial
      });
      
      // Reload status
      const newStatus = await getSubscriptionStatus();
      setStatus(newStatus);
      
      // Reset form
      setSelectedPlan(null);
      setSelectedPaymentMethod(null);
      setUseTrial(false);
      
      alert('Подписка активирована успешно!');
    } catch (error) {
      console.error('Error creating subscription:', error);
      alert('Ошибка при активации подписки');
    } finally {
      setProcessing(false);
    }
  };

  const canUseTrial = (planType: string) => {
    if (!status) return false;
    return !status.active_subscriptions.some(sub => sub.type === planType);
  };

  if (loading) {
    return (
      <div className="page">
        <Header title="Подписки" />
        <div className="loading">Загрузка...</div>
      </div>
    );
  }

  if (!plans || !status) {
    return (
      <div className="page">
        <Header title="Подписки" />
        <div className="error">Ошибка загрузки данных</div>
      </div>
    );
  }

  return (
    <div className="page">
      <Header title="Подписки" />
      
      <div className="subscription-page">
        {/* Current Status */}
        <div className="current-status">
          <h2>Текущий статус</h2>
          <div className="status-card">
            <div className="status-info">
              <div className="role">
                {status.is_premium && (
                  <img src={status.crown_icon_url} alt="Crown" className="crown-icon" />
                )}
                <span className={`role-badge ${status.role}`}>
                  {status.role === 'user' ? 'Обычный пользователь' : 
                   status.role === 'pro' ? 'Pro' : 
                   status.role === 'premium' ? 'Premium' : 'Администратор'}
                </span>
              </div>
              <div className="bonus-balance">
                <Icon name="star" />
                <span>{status.bonus_balance} бонусов</span>
              </div>
            </div>
          </div>
        </div>

        {/* Subscription Plans */}
        <div className="plans-section">
          <h2>Выберите подписку</h2>
          <div className="plans-grid">
            {Object.entries(plans.plans).map(([key, plan]) => (
              <div 
                key={key} 
                className={`plan-card ${selectedPlan === key ? 'selected' : ''}`}
                onClick={() => setSelectedPlan(key as 'pro' | 'premium')}
              >
                <div className="plan-header">
                  {key === 'premium' && (
                    <img src={plan.crown_icon_url} alt="Crown" className="crown-icon" />
                  )}
                  <h3>{plan.name}</h3>
                  <p className="plan-description">{plan.description}</p>
                </div>
                
                <div className="plan-pricing">
                  <div className="price">
                    <span className="amount">{plan.price_rub} ₽</span>
                    <span className="period">/месяц</span>
                  </div>
                  <div className="bonus-price">
                    или {plan.price_bonus} бонусов
                  </div>
                </div>

                <div className="plan-features">
                  <h4>Возможности:</h4>
                  <ul>
                    {Object.entries(plan.features).map(([feature, enabled]) => (
                      <li key={feature} className={enabled ? 'enabled' : 'disabled'}>
                        <Icon name={enabled ? 'check' : 'close'} />
                        {getFeatureName(feature)}
                      </li>
                    ))}
                  </ul>
                </div>

                {canUseTrial(key) && (
                  <div className="trial-option">
                    <label>
                      <input 
                        type="checkbox" 
                        checked={useTrial && selectedPlan === key}
                        onChange={(e) => setUseTrial(e.target.checked)}
                      />
                      Попробовать бесплатно 7 дней
                    </label>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Payment Methods */}
        {selectedPlan && (
          <div className="payment-section">
            <h2>Способ оплаты</h2>
            <div className="payment-methods">
              {plans.payment_methods.map((method) => (
                <div 
                  key={method.id}
                  className={`payment-method ${selectedPaymentMethod === method.id ? 'selected' : ''}`}
                  onClick={() => setSelectedPaymentMethod(method.id)}
                >
                  <img src={method.icon} alt={method.name} className="payment-icon" />
                  <span>{method.name}</span>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Subscribe Button */}
        {selectedPlan && selectedPaymentMethod && (
          <div className="subscribe-section">
            <button 
              className="subscribe-button"
              onClick={handleSubscribe}
              disabled={processing}
            >
              {processing ? 'Обработка...' : 
               useTrial ? 'Начать пробный период' : 'Оформить подписку'}
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

const getFeatureName = (feature: string): string => {
  const featureNames: Record<string, string> = {
    unlimited_catches: 'Неограниченные уловы',
    advanced_statistics: 'Расширенная статистика',
    priority_support: 'Приоритетная поддержка',
    ad_free: 'Без рекламы',
    create_points: 'Создание точек',
    manage_points: 'Управление точками',
    create_groups: 'Создание групп',
    moderate_groups: 'Модерация групп',
    priority_search: 'Приоритет в поиске',
    crown_badge: 'Корона у аватарки',
  };
  return featureNames[feature] || feature;
};

export default SubscriptionPage;
