<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class WeatherController extends Controller
{
    /**
     * @OA\Get(
     *     path="/weather",
     *     tags={"Weather"},
     *     summary="Получить данные о погоде",
     *     description="Возвращает текущую погоду или прогноз по координатам",
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         description="Широта",
     *         required=true,
     *         @OA\Schema(type="number", format="float", example=55.7558)
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         in="query",
     *         description="Долгота",
     *         required=true,
     *         @OA\Schema(type="number", format="float", example=37.6176)
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Дата в формате Y-m-d (опционально)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-15")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные о погоде",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="temperature", type="number", format="float", example=15.5),
     *                 @OA\Property(property="humidity", type="integer", example=65),
     *                 @OA\Property(property="pressure", type="number", format="float", example=1013.25),
     *                 @OA\Property(property="wind_speed", type="number", format="float", example=3.2),
     *                 @OA\Property(property="wind_direction", type="string", example="СЗ"),
     *                 @OA\Property(property="cloudiness", type="integer", example=40),
     *                 @OA\Property(property="precipitation", type="integer", example=0),
     *                 @OA\Property(property="description", type="string", example="Переменная облачность"),
     *                 @OA\Property(property="icon", type="string", example="partly-cloudy"),
     *                 @OA\Property(property="location", type="object",
     *                     @OA\Property(property="lat", type="number", format="float", example=55.7558),
     *                     @OA\Property(property="lng", type="number", format="float", example=37.6176),
     *                     @OA\Property(property="city", type="string", example="Москва")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверные параметры запроса",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Координаты обязательны")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка получения данных о погоде"
     *     )
     * )
     */
    public function getWeather(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $date = $request->input('date'); // Y-m-d format

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Координаты обязательны'
            ], 400);
        }

        try {
            // Используем OpenWeatherMap API для получения погоды
            $weatherData = $this->fetchWeatherData($lat, $lng, $date);
            
            return response()->json([
                'success' => true,
                'data' => $weatherData
            ]);
        } catch (\Exception $e) {
            Log::error('Weather API error', [
                'lat' => $lat,
                'lng' => $lng,
                'date' => $date,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось получить данные о погоде'
            ], 500);
        }
    }

    /**
     * Получить данные о погоде с внешнего API
     */
    private function fetchWeatherData($lat, $lng, $date = null)
    {
        // Для демонстрации возвращаем моковые данные
        // В реальном проекте здесь будет запрос к OpenWeatherMap API
        
        $weatherOptions = [
            'cloudiness' => ['ясно', 'малооблачно', 'облачно', 'пасмурно'],
            'precipitation' => ['без осадков', 'дождь', 'снег', 'град', 'туман'],
            'wind_direction' => ['С', 'СВ', 'В', 'ЮВ', 'Ю', 'ЮЗ', 'З', 'СЗ']
        ];

        // Генерируем случайные данные на основе координат для консистентности
        $seed = crc32($lat . $lng . $date);
        srand($seed);

        $temperature = rand(-20, 35) + (rand(0, 99) / 100);
        $pressure = rand(720, 780) + (rand(0, 99) / 100);
        $windSpeed = rand(0, 15) + (rand(0, 99) / 100);
        
        $cloudiness = $weatherOptions['cloudiness'][rand(0, count($weatherOptions['cloudiness']) - 1)];
        $precipitation = $weatherOptions['precipitation'][rand(0, count($weatherOptions['precipitation']) - 1)];
        $windDirection = $weatherOptions['wind_direction'][rand(0, count($weatherOptions['wind_direction']) - 1)];

        return [
            'temperature' => round($temperature, 1),
            'pressure' => round($pressure, 1),
            'wind_speed' => round($windSpeed, 1),
            'cloudiness' => $cloudiness,
            'precipitation' => $precipitation,
            'wind_direction' => $windDirection,
            'source' => 'mock', // Указываем, что это тестовые данные
            'coordinates' => [
                'lat' => $lat,
                'lng' => $lng
            ],
            'date' => $date
        ];
    }

    /**
     * @OA\Get(
     *     path="/weather/options",
     *     tags={"Weather"},
     *     summary="Получить варианты погодных условий",
     *     description="Возвращает список доступных вариантов для селектов погоды",
     *     @OA\Response(
     *         response=200,
     *         description="Варианты погодных условий",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="cloudiness", type="array", @OA\Items(
     *                     @OA\Property(property="value", type="string", example="ясно"),
     *                     @OA\Property(property="label", type="string", example="Ясно")
     *                 )),
     *                 @OA\Property(property="precipitation", type="array", @OA\Items(
     *                     @OA\Property(property="value", type="string", example="дождь"),
     *                     @OA\Property(property="label", type="string", example="Дождь")
     *                 )),
     *                 @OA\Property(property="wind_direction", type="array", @OA\Items(
     *                     @OA\Property(property="value", type="string", example="С"),
     *                     @OA\Property(property="label", type="string", example="Север")
     *                 ))
     *             )
     *         )
     *     )
     * )
     */
    public function getWeatherOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'cloudiness' => [
                    ['value' => 'ясно', 'label' => 'Ясно'],
                    ['value' => 'малооблачно', 'label' => 'Малооблачно'],
                    ['value' => 'облачно', 'label' => 'Облачно'],
                    ['value' => 'пасмурно', 'label' => 'Пасмурно']
                ],
                'precipitation' => [
                    ['value' => 'без осадков', 'label' => 'Без осадков'],
                    ['value' => 'дождь', 'label' => 'Дождь'],
                    ['value' => 'снег', 'label' => 'Снег'],
                    ['value' => 'град', 'label' => 'Град'],
                    ['value' => 'туман', 'label' => 'Туман']
                ],
                'wind_direction' => [
                    ['value' => 'С', 'label' => 'Север'],
                    ['value' => 'СВ', 'label' => 'Северо-восток'],
                    ['value' => 'В', 'label' => 'Восток'],
                    ['value' => 'ЮВ', 'label' => 'Юго-восток'],
                    ['value' => 'Ю', 'label' => 'Юг'],
                    ['value' => 'ЮЗ', 'label' => 'Юго-запад'],
                    ['value' => 'З', 'label' => 'Запад'],
                    ['value' => 'СЗ', 'label' => 'Северо-запад']
                ]
            ]
        ]);
    }
}