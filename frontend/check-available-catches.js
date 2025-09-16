#!/usr/bin/env node

/**
 * Скрипт для проверки доступных уловов в API
 */

const https = require('https');
const http = require('http');

const API_BASE = 'https://api.fishtrackpro.ru/api/v1';
const LOCAL_API_BASE = 'http://localhost:8000/api/v1';

function makeRequest(url) {
  return new Promise((resolve, reject) => {
    const client = url.startsWith('https') ? https : http;
    
    client.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          const json = JSON.parse(data);
          resolve({ status: res.statusCode, data: json });
        } catch (e) {
          reject(new Error('Invalid JSON response'));
        }
      });
    }).on('error', reject);
  });
}

async function checkCatches() {
  console.log('🔍 Проверка доступных уловов...\n');
  
  try {
    // Проверяем ленту
    console.log('📡 Получение ленты...');
    const feedResponse = await makeRequest(`${API_BASE}/feed?type=all&page=1&limit=10`);
    
    if (feedResponse.status === 200 && feedResponse.data.data) {
      const catches = feedResponse.data.data;
      console.log(`✅ Найдено ${catches.length} уловов в ленте:`);
      
      catches.forEach((catchRecord, index) => {
        console.log(`  ${index + 1}. ID: ${catchRecord.id}, Пользователь: ${catchRecord.user?.name || 'Неизвестно'}`);
      });
      
      // Проверяем первый улов
      if (catches.length > 0) {
        const firstCatch = catches[0];
        console.log(`\n🔍 Проверка деталей улова ID ${firstCatch.id}...`);
        
        try {
          const detailResponse = await makeRequest(`${API_BASE}/catch/${firstCatch.id}`);
          if (detailResponse.status === 200) {
            console.log(`✅ Улов ${firstCatch.id} успешно загружен`);
            console.log(`   Вид рыбы: ${detailResponse.data.data.fish_species?.name || 'Не указан'}`);
            console.log(`   Вес: ${detailResponse.data.data.weight || 'Не указан'} г`);
            console.log(`   Длина: ${detailResponse.data.data.length || 'Не указана'} см`);
          } else {
            console.log(`❌ Ошибка загрузки улова ${firstCatch.id}: ${detailResponse.status}`);
          }
        } catch (error) {
          console.log(`❌ Ошибка при проверке улова ${firstCatch.id}:`, error.message);
        }
      }
    } else {
      console.log(`❌ Ошибка получения ленты: ${feedResponse.status}`);
      console.log('Ответ:', JSON.stringify(feedResponse.data, null, 2));
    }
    
  } catch (error) {
    console.log('❌ Ошибка:', error.message);
  }
}

async function checkLocalAPI() {
  console.log('\n🏠 Проверка локального API...\n');
  
  try {
    const feedResponse = await makeRequest(`${LOCAL_API_BASE}/feed?type=all&page=1&limit=5`);
    
    if (feedResponse.status === 200 && feedResponse.data.data) {
      const catches = feedResponse.data.data;
      console.log(`✅ Локальный API: найдено ${catches.length} уловов`);
      
      catches.forEach((catchRecord, index) => {
        console.log(`  ${index + 1}. ID: ${catchRecord.id}`);
      });
    } else {
      console.log(`❌ Локальный API недоступен: ${feedResponse.status}`);
    }
    
  } catch (error) {
    console.log('❌ Локальный API недоступен:', error.message);
  }
}

// Запускаем проверки
async function main() {
  await checkCatches();
  await checkLocalAPI();
  
  console.log('\n📝 Рекомендации:');
  console.log('1. Используйте ID уловов из ленты для тестирования');
  console.log('2. Улов с ID 91 не существует в базе данных');
  console.log('3. Доступные ID начинаются с 218');
  console.log('4. Проверьте настройки API в config.ts');
}

main().catch(console.error);

