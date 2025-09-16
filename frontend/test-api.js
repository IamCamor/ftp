// Тест API для проверки функциональности погоды
const API_BASE = 'https://api.fishtrackpro.ru/api/v1';

// Получаем токен из localStorage (если запускаем в браузере)
function getToken() {
    if (typeof localStorage !== 'undefined') {
        return localStorage.getItem('token');
    }
    return 'simple_token_1_17578'; // Используем токен из логов
}

async function testWeatherAPI() {
    const token = getToken();
    console.log('Используем токен:', token ? token.substring(0, 20) + '...' : 'Нет токена');

    try {
        // 1. Проверяем загрузку избранных мест
        console.log('\n1. Загружаем избранные места...');
        const favsResponse = await fetch(`${API_BASE}/weather/favs`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const favsData = await favsResponse.json();
        console.log('Статус:', favsResponse.status);
        console.log('Данные избранных мест:', favsData);

        // 2. Пытаемся добавить новое место
        console.log('\n2. Добавляем новое место...');
        const addResponse = await fetch(`${API_BASE}/weather/favs`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                lat: 55.7558,
                lng: 37.6176,
                label: 'Тестовое место из скрипта'
            })
        });

        const addData = await addResponse.json();
        console.log('Статус добавления:', addResponse.status);
        console.log('Результат добавления:', addData);

        // 3. Получаем данные о погоде
        console.log('\n3. Получаем данные о погоде...');
        const weatherResponse = await fetch(`${API_BASE}/weather?lat=55.7558&lng=37.6176`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const weatherData = await weatherResponse.json();
        console.log('Статус погоды:', weatherResponse.status);
        console.log('Данные о погоде:', weatherData);

        // 4. Проверяем, что место добавилось
        console.log('\n4. Проверяем обновленный список мест...');
        const favsResponse2 = await fetch(`${API_BASE}/weather/favs`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const favsData2 = await favsResponse2.json();
        console.log('Обновленный список мест:', favsData2);

    } catch (error) {
        console.error('Ошибка при тестировании API:', error);
    }
}

// Запускаем тест
if (typeof window !== 'undefined') {
    // Если запускаем в браузере
    window.testWeatherAPI = testWeatherAPI;
    console.log('Тест готов! Запустите testWeatherAPI() в консоли браузера');
} else {
    // Если запускаем в Node.js
    testWeatherAPI();
}

