<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle Telegram webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('Telegram webhook received', $data);

            // Verify webhook secret if configured
            if (config('telegram.webhook.enabled') && config('telegram.webhook.secret_token')) {
                $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');
                if ($secret !== config('telegram.webhook.secret_token')) {
                    Log::warning('Invalid Telegram webhook secret');
                    return response()->json(['error' => 'Invalid secret'], 401);
                }
            }

            // Handle different types of updates
            if (isset($data['message'])) {
                $this->handleMessage($data['message']);
            } elseif (isset($data['callback_query'])) {
                $this->handleCallbackQuery($data['callback_query']);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle incoming message
     */
    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];

        Log::info('Telegram message received', [
            'chat_id' => $chatId,
            'text' => $text,
            'from' => $from
        ]);

        // Check if message is a command
        if (str_starts_with($text, '/')) {
            $command = substr($text, 1);
            $response = $this->telegramService->handleCommand($command);
            $this->telegramService->sendMessage($response, (string)$chatId);
        }
    }

    /**
     * Handle callback query (inline keyboard buttons)
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'] ?? '';
        $from = $callbackQuery['from'] ?? [];

        Log::info('Telegram callback query received', [
            'chat_id' => $chatId,
            'data' => $data,
            'from' => $from
        ]);

        // Handle different callback data
        switch ($data) {
            case 'stats':
                $response = $this->telegramService->handleCommand('stats');
                break;
            case 'users':
                $response = $this->telegramService->handleCommand('users');
                break;
            case 'catches':
                $response = $this->telegramService->handleCommand('catches');
                break;
            case 'payments':
                $response = $this->telegramService->handleCommand('payments');
                break;
            case 'points':
                $response = $this->telegramService->handleCommand('points');
                break;
            default:
                $response = "Unknown command.";
        }

        $this->telegramService->sendMessage($response, (string)$chatId);
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(Request $request): JsonResponse
    {
        try {
            $webhookUrl = $request->input('url');
            
            if (!$webhookUrl) {
                return response()->json(['error' => 'URL is required'], 400);
            }

            $response = $this->telegramService->setWebhook($webhookUrl);
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to set Telegram webhook', [
                'error' => $e->getMessage(),
                'url' => $request->input('url')
            ]);
            
            return response()->json(['error' => 'Failed to set webhook'], 500);
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): JsonResponse
    {
        try {
            $response = $this->telegramService->getWebhookInfo();
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to get Telegram webhook info', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get webhook info'], 500);
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): JsonResponse
    {
        try {
            $response = $this->telegramService->deleteWebhook();
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Failed to delete Telegram webhook', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to delete webhook'], 500);
        }
    }

    /**
     * Test notification
     */
    public function testNotification(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'test');
            $chatId = $request->input('chat_id');
            
            $message = "🧪 *Test Notification*\n\n" .
                      "Type: {$type}\n" .
                      "Time: " . now()->format('Y-m-d H:i:s') . "\n" .
                      "Status: ✅ Working correctly";
            
            $success = $this->telegramService->sendMessage($message, $chatId);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Test notification sent' : 'Failed to send notification'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send test notification', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to send test notification'], 500);
        }
    }
}
