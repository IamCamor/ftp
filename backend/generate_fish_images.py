#!/usr/bin/env python3
"""
Скрипт для генерации тестовых изображений рыб
"""

import requests
import json
import os
from PIL import Image, ImageDraw, ImageFont
import random

def generate_fish_image(fish_type, index, output_dir="public/images/fish"):
    """Генерирует изображение рыбы с текстом"""
    
    # Создаем директорию если не существует
    os.makedirs(output_dir, exist_ok=True)
    
    # Размеры изображения
    width, height = 800, 600
    
    # Создаем изображение с градиентом (имитация воды)
    image = Image.new('RGB', (width, height), (135, 206, 235))  # Небесно-голубой
    draw = ImageDraw.Draw(image)
    
    # Добавляем градиент (имитация воды)
    for y in range(height):
        # Градиент от светло-голубого к темно-синему
        r = int(135 + (y / height) * 50)
        g = int(206 + (y / height) * 30)
        b = int(235 + (y / height) * 20)
        draw.line([(0, y), (width, y)], fill=(r, g, b))
    
    # Добавляем волны
    for i in range(5):
        wave_y = height - 100 + i * 20
        for x in range(0, width, 10):
            wave_offset = int(10 * (i % 2))
            draw.ellipse([x + wave_offset, wave_y, x + 20 + wave_offset, wave_y + 10], 
                        fill=(100, 149, 237, 50), outline=(70, 130, 180))
    
    # Рисуем силуэт рыбы
    fish_x = width // 2
    fish_y = height // 2
    
    # Тело рыбы (овал)
    fish_width = 200
    fish_height = 80
    fish_left = fish_x - fish_width // 2
    fish_top = fish_y - fish_height // 2
    fish_right = fish_x + fish_width // 2
    fish_bottom = fish_y + fish_height // 2
    
    # Цвет рыбы в зависимости от типа
    fish_colors = {
        'Щука': (34, 139, 34),      # Зеленый
        'Окунь': (255, 165, 0),     # Оранжевый
        'Лещ': (192, 192, 192),     # Серебряный
        'Плотва': (255, 192, 203),  # Розовый
        'Карась': (255, 215, 0),    # Золотой
        'Карп': (210, 180, 140),    # Бронзовый
        'Сазан': (160, 82, 45),     # Коричневый
        'Сом': (105, 105, 105),     # Серый
        'Судак': (70, 130, 180),    # Стальной
        'Жерех': (255, 255, 255),   # Белый
    }
    
    fish_color = fish_colors.get(fish_type, (100, 149, 237))
    
    # Рисуем тело рыбы
    draw.ellipse([fish_left, fish_top, fish_right, fish_bottom], 
                fill=fish_color, outline=(0, 0, 0), width=2)
    
    # Хвост
    tail_points = [
        (fish_right, fish_y),
        (fish_right + 60, fish_y - 30),
        (fish_right + 60, fish_y + 30)
    ]
    draw.polygon(tail_points, fill=fish_color, outline=(0, 0, 0))
    
    # Плавники
    # Спинной плавник
    draw.ellipse([fish_left + 20, fish_top - 20, fish_right - 20, fish_top + 10], 
                fill=fish_color, outline=(0, 0, 0))
    
    # Брюшные плавники
    draw.ellipse([fish_left + 30, fish_bottom - 10, fish_left + 80, fish_bottom + 20], 
                fill=fish_color, outline=(0, 0, 0))
    draw.ellipse([fish_right - 80, fish_bottom - 10, fish_right - 30, fish_bottom + 20], 
                fill=fish_color, outline=(0, 0, 0))
    
    # Глаз
    eye_x = fish_left + 40
    eye_y = fish_y - 10
    draw.ellipse([eye_x - 8, eye_y - 8, eye_x + 8, eye_y + 8], 
                fill=(255, 255, 255), outline=(0, 0, 0))
    draw.ellipse([eye_x - 4, eye_y - 4, eye_x + 4, eye_y + 4], 
                fill=(0, 0, 0))
    
    # Чешуя (декоративные линии)
    for i in range(5):
        scale_y = fish_top + 20 + i * 10
        draw.arc([fish_left + 20, scale_y - 5, fish_right - 20, scale_y + 5], 
                0, 180, fill=(0, 0, 0), width=1)
    
    # Добавляем текст
    try:
        # Пытаемся использовать системный шрифт
        font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 24)
    except:
        try:
            font = ImageFont.truetype("arial.ttf", 24)
        except:
            font = ImageFont.load_default()
    
    # Текст с названием рыбы
    text = f"{fish_type}"
    bbox = draw.textbbox((0, 0), text, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    
    text_x = (width - text_width) // 2
    text_y = height - 50
    
    # Тень для текста
    draw.text((text_x + 2, text_y + 2), text, fill=(0, 0, 0), font=font)
    # Основной текст
    draw.text((text_x, text_y), text, fill=(255, 255, 255), font=font)
    
    # Добавляем информацию об улове
    info_text = f"Улов #{index}"
    bbox = draw.textbbox((0, 0), info_text, font=font)
    info_width = bbox[2] - bbox[0]
    info_x = (width - info_width) // 2
    info_y = text_y + 30
    
    draw.text((info_x + 1, info_y + 1), info_text, fill=(0, 0, 0), font=font)
    draw.text((info_x, info_y), info_text, fill=(255, 255, 255), font=font)
    
    # Сохраняем изображение
    filename = f"{fish_type.lower().replace(' ', '_')}_{index}.jpg"
    filepath = os.path.join(output_dir, filename)
    image.save(filepath, "JPEG", quality=85)
    
    return f"/images/fish/{filename}"

def main():
    """Основная функция"""
    print("🐟 Генерация тестовых изображений рыб...")
    
    fish_types = [
        'Щука', 'Окунь', 'Лещ', 'Плотва', 'Карась', 'Карп', 
        'Сазан', 'Сом', 'Судак', 'Жерех', 'Голавль', 'Язь',
        'Линь', 'Красноперка', 'Уклейка', 'Ерш', 'Пескарь'
    ]
    
    # Создаем 50 изображений
    image_urls = []
    for i in range(1, 51):
        fish_type = random.choice(fish_types)
        url = generate_fish_image(fish_type, i)
        image_urls.append({
            'index': i,
            'fish_type': fish_type,
            'url': url
        })
        print(f"✅ Создано изображение {i}/50: {fish_type}")
    
    # Сохраняем список URL в JSON файл
    with open('fish_images.json', 'w', encoding='utf-8') as f:
        json.dump(image_urls, f, ensure_ascii=False, indent=2)
    
    print(f"\n🎉 Создано {len(image_urls)} изображений рыб!")
    print("📁 Изображения сохранены в public/images/fish/")
    print("📄 Список URL сохранен в fish_images.json")

if __name__ == "__main__":
    main()

