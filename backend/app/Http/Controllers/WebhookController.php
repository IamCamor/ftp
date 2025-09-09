<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use App\Services\TelegramService;

class WebhookController extends Controller
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }
    /**
     * Handle GitHub webhook for deployment
     */
    public function github(Request $request): JsonResponse
    {
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        
        // Verify webhook signature
        if (!$this->verifySignature($signature, $payload)) {
            Log::warning('GitHub webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        $event = $request->header('X-GitHub-Event');
        $data = $request->json()->all();
        
        Log::info('GitHub webhook received', [
            'event' => $event,
            'repository' => $data['repository']['full_name'] ?? 'unknown',
            'ref' => $data['ref'] ?? 'unknown'
        ]);
        
        // Only deploy on push to main branch
        if ($event === 'push' && ($data['ref'] ?? '') === 'refs/heads/main') {
            $this->deploy($data);
        }
        
        return response()->json(['status' => 'success']);
    }
    
    /**
     * Verify GitHub webhook signature
     */
    private function verifySignature(string $signature, string $payload): bool
    {
        $secret = config('app.github_webhook_secret');
        
        if (!$secret) {
            Log::warning('GitHub webhook secret not configured');
            return false;
        }
        
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Deploy the application
     */
    private function deploy(array $data = []): void
    {
        Log::info('Starting deployment...');
        
        try {
            // Pull latest changes
            $this->runCommand('git pull origin main');
            
            // Install/update Composer dependencies
            $this->runCommand('composer install --no-dev --optimize-autoloader');
            
            // Run database migrations
            Artisan::call('migrate', ['--force' => true]);
            
            // Clear and cache configurations
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            // Install/update NPM dependencies and build frontend
            $this->runCommand('cd frontend && npm ci && npm run build');
            
            // Restart services
            $this->runCommand('sudo systemctl reload nginx');
            $this->runCommand('sudo systemctl restart php8.2-fpm');
            
            Log::info('Deployment completed successfully');
            
            // Send success notification
            $this->sendDeploymentNotification($data, 'success');
            
        } catch (\Exception $e) {
            Log::error('Deployment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Send failure notification
            $this->sendDeploymentNotification($data, 'failed', $e->getMessage());
            
            throw $e;
        }
    }
    
    /**
     * Run shell command
     */
    private function runCommand(string $command): void
    {
        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(300); // 5 minutes timeout
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new \Exception("Command failed: {$command}\nOutput: {$process->getOutput()}\nError: {$process->getErrorOutput()}");
        }
        
        Log::info("Command executed successfully: {$command}");
    }
    
    /**
     * Send deployment notification
     */
    private function sendDeploymentNotification(array $data, string $status, string $error = null): void
    {
        try {
            $repository = $data['repository']['full_name'] ?? 'unknown';
            $branch = str_replace('refs/heads/', '', $data['ref'] ?? 'unknown');
            $commitMessage = $data['head_commit']['message'] ?? 'No message';
            $author = $data['head_commit']['author']['name'] ?? 'Unknown';
            
            $notificationData = [
                '{repository}' => $repository,
                '{branch}' => $branch,
                '{commit_message}' => $commitMessage,
                '{author}' => $author,
                '{status}' => $status === 'success' ? '✅ Success' : '❌ Failed',
                '{error}' => $error,
            ];
            
            $this->telegramService->sendDeploymentNotification($notificationData);
        } catch (\Exception $e) {
            Log::error('Failed to send deployment notification', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0')
        ]);
    }
}
