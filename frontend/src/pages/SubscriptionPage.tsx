import React, { useState, useEffect } from 'react';
import { getSubscriptionPlans, getSubscriptionStatus, createSubscription, applyPromoCode, applyInviteCode } from '../api';
import type { SubscriptionPlansResponse, SubscriptionStatus } from '../types';
import Icon from '../components/Icon';
import PageHeader from '../components/PageHeader';

const SubscriptionPage: React.FC = () => {
  const [plans, setPlans] = useState<SubscriptionPlansResponse | null>(null);
  const [status, setStatus] = useState<SubscriptionStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedPlan, setSelectedPlan] = useState<'pro' | 'premium' | 'guide' | null>(null);
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<string | null>(null);
  const [useTrial, setUseTrial] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [promoCode, setPromoCode] = useState('');
  const [inviteCode, setInviteCode] = useState('');
  const [appliedPromo, setAppliedPromo] = useState<any>(null);
  const [appliedInvite, setAppliedInvite] = useState<any>(null);
  const [finalPrice, setFinalPrice] = useState<number | null>(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      // Check if user is authenticated
      const token = localStorage.getItem('token');
      if (!token) {
        console.log('User not authenticated, skipping subscription data load');
        setLoading(false);
        return;
      }

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

  const handleApplyPromoCode = async () => {
    if (!promoCode || !selectedPlan) return;
    
    try {
      const response = await applyPromoCode({
        code: promoCode,
        subscription_type: selectedPlan
      });
      
      if (response.success) {
        setAppliedPromo(response.data);
        setFinalPrice(response.data.final_price);
        setAppliedInvite(null); // Сбрасываем инвайт, если был применен промокод
      }
    } catch (error) {
      console.error('Error applying promo code:', error);
      alert('Промокод не найден или недействителен');
    }
  };

  const handleApplyInviteCode = async () => {
    if (!inviteCode || !selectedPlan) return;
    
    try {
      const response = await applyInviteCode({
        code: inviteCode,
        subscription_type: selectedPlan
      });
      
      if (response.success) {
        setAppliedInvite(response.data);
        setFinalPrice(response.data.final_price);
        setAppliedPromo(null); // Сбрасываем промокод, если был применен инвайт
      }
    } catch (error) {
      console.error('Error applying invite code:', error);
      alert('Инвайт код не найден или недействителен');
    }
  };

  const getPlanPrice = () => {
    if (!plans || !selectedPlan) return 0;
    const plan = plans.plans[selectedPlan as keyof typeof plans.plans];
    return plan?.price_rub || 0;
  };

  const calculateFinalPrice = () => {
    const basePrice = getPlanPrice();
    if (finalPrice !== null) return finalPrice;
    return basePrice;
  };

  if (loading) {
    return (
      <div className="page">
        <PageHeader title="Подписки" />
        <div className="loading">Загрузка...</div>
      </div>
    );
  }

  if (!plans || !status) {
    return (
      <div className="page">
        <PageHeader title="Подписки" />
        <div className="error">Ошибка загрузки данных</div>
      </div>
    );
  }

  return (
    <div className="page">
      <PageHeader title="Подписки" />
      
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
                   status.role === 'premium' ? 'Premium' : 
                   status.role === 'guide' ? 'Рыболовный гид' : 'Администратор'}
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
            {plans?.plans ? Object.entries(plans.plans).map(([key, plan]) => (
              <div 
                key={key} 
                className={`plan-card ${selectedPlan === key ? 'selected' : ''}`}
                onClick={() => setSelectedPlan(key as 'pro' | 'premium' | 'guide')}
              >
                <div className="plan-header">
                  {key === 'premium' && 'crown_icon_url' in plan && (
                    <img src={plan.crown_icon_url} alt="Crown" className="crown-icon" />
                  )}
                  {key === 'guide' && 'icon_url' in plan && (
                    <img src={plan.icon_url} alt="Guide" className="guide-icon" />
                  )}
                  <h3>{plan.name}</h3>
                  <p className="plan-description">{plan.description}</p>
                </div>
                
                <div className="plan-pricing">
                  <div className="price">
                    <span className="amount">{plan.price_rub} ₽</span>
                    <span className="period">/месяц</span>
                  </div>
                  {'price_bonus' in plan && (
                    <div className="bonus-price">
                      или {plan.price_bonus} бонусов
                    </div>
                  )}
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
            )) : (
              <div className="loading-plans">
                <p>Загрузка планов подписки...</p>
              </div>
            )}
          </div>
        </div>

        {/* Promo Codes and Invites */}
        {selectedPlan && (
          <div className="discount-section">
            <h3>Промокоды и скидки</h3>
            
            <div className="discount-inputs">
              <div className="promo-code-input">
                <label>Промокод</label>
                <div className="input-group">
                  <input
                    type="text"
                    value={promoCode}
                    onChange={(e) => setPromoCode(e.target.value)}
                    placeholder="Введите промокод"
                    disabled={!!appliedInvite}
                  />
                  <button 
                    className="btn btn-secondary"
                    onClick={handleApplyPromoCode}
                    disabled={!promoCode || !!appliedInvite}
                  >
                    Применить
                  </button>
                </div>
                {appliedPromo && (
                  <div className="applied-discount">
                    <Icon name="check" size="sm" />
                    <span>Промокод применен! Скидка: {appliedPromo.discount}%</span>
                    <button 
                      className="remove-btn"
                      onClick={() => {
                        setAppliedPromo(null);
                        setFinalPrice(null);
                      }}
                    >
                      <Icon name="close" size="sm" />
                    </button>
                  </div>
                )}
              </div>
              
              <div className="invite-code-input">
                <label>Инвайт код</label>
                <div className="input-group">
                  <input
                    type="text"
                    value={inviteCode}
                    onChange={(e) => setInviteCode(e.target.value)}
                    placeholder="Введите инвайт код"
                    disabled={!!appliedPromo}
                  />
                  <button 
                    className="btn btn-secondary"
                    onClick={handleApplyInviteCode}
                    disabled={!inviteCode || !!appliedPromo}
                  >
                    Применить
                  </button>
                </div>
                {appliedInvite && (
                  <div className="applied-discount">
                    <Icon name="check" size="sm" />
                    <span>Инвайт код применен! Скидка: {appliedInvite.discount}%</span>
                    <button 
                      className="remove-btn"
                      onClick={() => {
                        setAppliedInvite(null);
                        setFinalPrice(null);
                      }}
                    >
                      <Icon name="close" size="sm" />
                    </button>
                  </div>
                )}
              </div>
            </div>
            
            <div className="price-summary">
              <div className="price-breakdown">
                <div className="base-price">
                  <span>Базовая цена:</span>
                  <span>{getPlanPrice()} ₽</span>
                </div>
                {(appliedPromo || appliedInvite) && (
                  <div className="discount-amount">
                    <span>Скидка:</span>
                    <span>-{getPlanPrice() - calculateFinalPrice()} ₽</span>
                  </div>
                )}
                <div className="final-price">
                  <span>Итого к оплате:</span>
                  <span className="total-amount">{calculateFinalPrice()} ₽</span>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Payment Methods */}
        {selectedPlan && (
          <div className="payment-section">
            <h2>Способ оплаты</h2>
            <div className="payment-methods">
              {plans?.payment_methods?.map((method) => (
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
