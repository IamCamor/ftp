import React, { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { ArrowLeft, Send, AlertTriangle } from 'lucide-react';
import Icon from '../components/Icon';

interface ReportData {
  type: 'spam' | 'inappropriate' | 'harassment' | 'violence' | 'other';
  description: string;
  entityType: 'catch' | 'track' | 'user' | 'comment';
  entityId: string;
}

const ReportPage: React.FC = () => {
  const navigate = useNavigate();
  const location = useLocation();
  const [reportData, setReportData] = useState<ReportData>({
    type: 'spam',
    description: '',
    entityType: 'catch',
    entityId: ''
  });
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  // Получаем данные из state навигации
  const { entityType, entityId, entityTitle } = location.state || {};

  React.useEffect(() => {
    if (entityType && entityId) {
      setReportData(prev => ({
        ...prev,
        entityType,
        entityId
      }));
    }
  }, [entityType, entityId]);

  const reportTypes = [
    { value: 'spam', label: 'Спам', description: 'Повторяющиеся или нежелательные сообщения' },
    { value: 'inappropriate', label: 'Неподходящий контент', description: 'Контент, нарушающий правила сообщества' },
    { value: 'harassment', label: 'Преследование', description: 'Угрозы или запугивание' },
    { value: 'violence', label: 'Насилие', description: 'Контент, содержащий насилие' },
    { value: 'other', label: 'Другое', description: 'Иная причина' }
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const response = await fetch('/api/v1/reports', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(reportData)
      });

      if (response.ok) {
        setSubmitted(true);
        setTimeout(() => {
          navigate(-1);
        }, 2000);
      } else {
        throw new Error('Failed to submit report');
      }
    } catch (error) {
      console.error('Error submitting report:', error);
      alert('Ошибка при отправке жалобы. Попробуйте еще раз.');
    } finally {
      setSubmitting(false);
    }
  };

  if (submitted) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4 text-center">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Icon name="check_circle" size="lg" className="text-green-600" />
          </div>
          <h2 className="text-xl font-semibold text-gray-900 mb-2">
            Жалоба отправлена
          </h2>
          <p className="text-gray-600 mb-4">
            Спасибо за обращение. Мы рассмотрим вашу жалобу в ближайшее время.
          </p>
          <button
            onClick={() => navigate(-1)}
            className="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Вернуться назад
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b">
        <div className="max-w-4xl mx-auto px-4 py-4">
          <div className="flex items-center space-x-4">
            <button
              onClick={() => navigate(-1)}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <ArrowLeft size={20} />
            </button>
            <div className="flex items-center space-x-2">
              <AlertTriangle className="text-red-500" size={24} />
              <h1 className="text-xl font-semibold text-gray-900">
                Пожаловаться
              </h1>
            </div>
          </div>
        </div>
      </div>

      {/* Content */}
      <div className="max-w-4xl mx-auto px-4 py-6">
        <div className="bg-white rounded-lg shadow-sm p-6">
          {entityTitle && (
            <div className="mb-6 p-4 bg-gray-50 rounded-lg">
              <h3 className="font-medium text-gray-900 mb-1">Жалоба на:</h3>
              <p className="text-gray-600">{entityTitle}</p>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Тип жалобы */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-3">
                Тип нарушения
              </label>
              <div className="space-y-3">
                {reportTypes.map((type) => (
                  <label key={type.value} className="flex items-start space-x-3 cursor-pointer">
                    <input
                      type="radio"
                      name="type"
                      value={type.value}
                      checked={reportData.type === type.value}
                      onChange={(e) => setReportData(prev => ({
                        ...prev,
                        type: e.target.value as ReportData['type']
                      }))}
                      className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    />
                    <div className="flex-1">
                      <div className="text-sm font-medium text-gray-900">
                        {type.label}
                      </div>
                      <div className="text-sm text-gray-500">
                        {type.description}
                      </div>
                    </div>
                  </label>
                ))}
              </div>
            </div>

            {/* Описание */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Дополнительная информация
              </label>
              <textarea
                value={reportData.description}
                onChange={(e) => setReportData(prev => ({
                  ...prev,
                  description: e.target.value
                }))}
                placeholder="Опишите проблему более подробно (необязательно)"
                rows={4}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              />
            </div>

            {/* Кнопки */}
            <div className="flex space-x-4 pt-4">
              <button
                type="button"
                onClick={() => navigate(-1)}
                className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Отмена
              </button>
              <button
                type="submit"
                disabled={submitting}
                className="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center space-x-2"
              >
                {submitting ? (
                  <>
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                    <span>Отправка...</span>
                  </>
                ) : (
                  <>
                    <Send size={16} />
                    <span>Отправить жалобу</span>
                  </>
                )}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default ReportPage;

