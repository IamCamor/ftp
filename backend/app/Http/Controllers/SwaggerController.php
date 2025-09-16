<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="FishTrackPro API",
 *     version="1.0.0",
 *     description="API для приложения FishTrackPro - социальной сети для рыболовов",
 *     @OA\Contact(
 *         email="support@fishtrackpro.ru",
 *         name="FishTrackPro Support",
 *         url="https://fishtrackpro.ru"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="https://api.fishtrackpro.ru/api/v1",
 *     description="Production server"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Development server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="jwt",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Bearer token для аутентификации"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Аутентификация и авторизация"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="Управление пользователями"
 * )
 * 
 * @OA\Tag(
 *     name="Catches",
 *     description="Управление уловами"
 * )
 * 
 * @OA\Tag(
 *     name="Feed",
 *     description="Лента уловов"
 * )
 * 
 * @OA\Tag(
 *     name="Points",
 *     description="Точки на карте"
 * )
 * 
 * @OA\Tag(
 *     name="Weather",
 *     description="Погодные данные"
 * )
 * 
 * @OA\Tag(
 *     name="Events",
 *     description="События и мероприятия"
 * )
 * 
 * @OA\Tag(
 *     name="Groups",
 *     description="Группы пользователей"
 * )
 * 
 * @OA\Tag(
 *     name="Admin",
 *     description="Административные функции"
 * )
 * 
 * @OA\Tag(
 *     name="AI",
 *     description="ИИ сервисы"
 * )
 */
class SwaggerController extends Controller
{
    // Этот контроллер используется только для аннотаций Swagger
}
