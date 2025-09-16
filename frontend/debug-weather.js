// Отладочный скрипт для проверки функциональности погоды
console.log('🔍 Начинаем отладку функциональности погоды...');

// Проверяем, что мы на правильной странице
if (window.location.pathname !== '/weather') {
    console.log('❌ Не на странице погоды. Переходим...');
    window.location.href = '/weather';
} else {
    console.log('✅ На странице погоды');
    
    // Ждем загрузки страницы
    setTimeout(() => {
        console.log('🔍 Проверяем состояние страницы...');
        
        // Проверяем наличие элементов
        const addButton = document.querySelector('button:has-text("По названию")');
        const refreshButton = document.querySelector('button:has-text("Обновить")');
        const weatherCards = document.querySelectorAll('.weather-card');
        
        console.log('Кнопка "По названию":', addButton ? '✅ Найдена' : '❌ Не найдена');
        console.log('Кнопка "Обновить":', refreshButton ? '✅ Найдена' : '❌ Не найдена');
        console.log('Карточки погоды:', weatherCards.length);
        
        // Проверяем localStorage
        const token = localStorage.getItem('token');
        console.log('Токен в localStorage:', token ? '✅ Есть' : '❌ Нет');
        
        // Проверяем консольные логи
        console.log('🔍 Ищем логи о погоде...');
        
        // Пытаемся добавить место программно
        if (addButton) {
            console.log('🔄 Пытаемся добавить тестовое место...');
            addButton.click();
            
            setTimeout(() => {
                const modal = document.querySelector('.modal-content');
                if (modal) {
                    console.log('✅ Модальное окно открылось');
                    
                    const input = modal.querySelector('input[type="text"]');
                    if (input) {
                        input.value = 'Тестовое место для отладки';
                        console.log('✅ Ввели название места');
                        
                        const saveButton = modal.querySelector('button:has-text("Добавить место")');
                        if (saveButton) {
                            console.log('🔄 Пытаемся сохранить место...');
                            saveButton.click();
                        } else {
                            console.log('❌ Кнопка сохранения не найдена');
                        }
                    } else {
                        console.log('❌ Поле ввода не найдено');
                    }
                } else {
                    console.log('❌ Модальное окно не открылось');
                }
            }, 1000);
        }
        
        // Проверяем ошибки в консоли
        const originalError = console.error;
        console.error = function(...args) {
            if (args.some(arg => typeof arg === 'string' && arg.includes('weather'))) {
                console.log('🚨 Ошибка связанная с погодой:', args);
            }
            originalError.apply(console, args);
        };
        
    }, 2000);
}

// Функция для проверки API
async function testWeatherAPI() {
    console.log('🔍 Тестируем API погоды...');
    
    const token = localStorage.getItem('token');
    if (!token) {
        console.log('❌ Токен не найден');
        return;
    }
    
    try {
        // Загружаем избранные места
        console.log('📡 Загружаем избранные места...');
        const response = await fetch('https://api.fishtrackpro.ru/api/v1/weather/favs', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        console.log('📊 Результат загрузки:', {
            status: response.status,
            data: data
        });
        
        // Пытаемся добавить место
        console.log('📡 Добавляем тестовое место...');
        const addResponse = await fetch('https://api.fishtrackpro.ru/api/v1/weather/favs', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                lat: 55.7558,
                lng: 37.6176,
                label: 'Тестовое место API'
            })
        });
        
        const addData = await addResponse.json();
        console.log('📊 Результат добавления:', {
            status: addResponse.status,
            data: addData
        });
        
    } catch (error) {
        console.log('❌ Ошибка API:', error);
    }
}

// Делаем функцию доступной глобально
window.testWeatherAPI = testWeatherAPI;
console.log('💡 Для тестирования API запустите: testWeatherAPI()');

