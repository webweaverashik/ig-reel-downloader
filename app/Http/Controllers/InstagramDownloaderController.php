<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstagramDownloaderController extends Controller
{
    /**
     * Display the Instagram downloader page
     */
    public function index()
    {
        return view('instagram-downloader');
    }

    /**
     * Fetch Instagram media using Python worker
     */
    public function fetch(Request $request)
    {
        // Validate the URL
        $validator = Validator::make($request->all(), [
            'url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidInstagramUrl($value)) {
                        $fail('Please provide a valid Instagram URL (reel, video, or photo).');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ],
                422,
            );
        }

        $url = $request->input('url');

        try {
            $result = $this->executePythonWorker($url);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Instagram fetch error: ' . $e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => $this->getErrorMessage($e->getMessage()),
                ],
                500,
            );
        }
    }

    /**
     * Validate Instagram URL format
     */
    private function isValidInstagramUrl(string $url): bool
    {
        $patterns = ['/^https?:\/\/(www\.)?instagram\.com\/p\/[\w-]+\/?/', '/^https?:\/\/(www\.)?instagram\.com\/reel\/[\w-]+\/?/', '/^https?:\/\/(www\.)?instagram\.com\/reels\/[\w-]+\/?/', '/^https?:\/\/(www\.)?instagram\.com\/tv\/[\w-]+\/?/', '/^https?:\/\/(www\.)?instagram\.com\/[\w.]+\/reel\/[\w-]+\/?/'];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the correct Python executable
     */
    private function findPythonExecutable(): string
    {
        // First, check if explicitly set in config/env
        $configuredPath = config('instagram.python_path');
        if ($configuredPath && $configuredPath !== 'auto') {
            return $configuredPath;
        }

        // List of possible Python executable names/paths
        $possiblePaths = [];

        // Check operating system
        if (PHP_OS_FAMILY === 'Windows') {
            $possiblePaths = ['python', 'python3', 'py', 'py -3', 'C:\\Python312\\python.exe', 'C:\\Python311\\python.exe', 'C:\\Python310\\python.exe', 'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe', 'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe', 'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe'];
        } else {
            // Linux/macOS
            $possiblePaths = ['python3', 'python', '/usr/bin/python3', '/usr/bin/python', '/usr/local/bin/python3', '/usr/local/bin/python'];
        }

        // Test each path
        foreach ($possiblePaths as $path) {
            if ($this->isPythonExecutableValid($path)) {
                Log::info("Found Python executable: {$path}");
                return $path;
            }
        }

        // Default fallback
        return PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
    }

    /**
     * Check if a Python executable path is valid
     */
    private function isPythonExecutableValid(string $path): bool
    {
        try {
            $process = new Process([$path, '--version']);
            $process->setTimeout(5);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Execute the Python worker script
     */
    private function executePythonWorker(string $url): array
    {
        $pythonPath = $this->findPythonExecutable();
        $scriptPath = base_path('python_worker/instagram_fetch.py');

        // Ensure script exists
        if (! file_exists($scriptPath)) {
            throw new \Exception('Python worker script not found at: ' . $scriptPath);
        }

        Log::info("Using Python: {$pythonPath}");
        Log::info("Script path: {$scriptPath}");
        Log::info("Processing URL: {$url}");

        $process = new Process([$pythonPath, $scriptPath, $url]);

        $process->setTimeout(60);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $errorOutput = $process->getErrorOutput();
            Log::error("Python process failed: {$errorOutput}");
            throw new \Exception($errorOutput ?: 'Failed to process Instagram URL');
        }

        $output = $process->getOutput();
        Log::info("Python output: {$output}");

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decode error: ' . json_last_error_msg());
            throw new \Exception('Invalid response from Python worker: ' . $output);
        }

        if (isset($result['error'])) {
            throw new \Exception($result['error']);
        }

        return $result;
    }

    /**
     * Get user-friendly error message
     */
    private function getErrorMessage(string $error): string
    {
        $errorMap = [
            'private'        => 'This content is from a private account and cannot be downloaded.',
            'not exist'      => 'This post no longer exists or has been removed.',
            'rate limit'     => 'Too many requests. Please try again in a few minutes.',
            'login required' => 'This content requires authentication and cannot be downloaded.',
            'unavailable'    => 'This content is currently unavailable.',
            'python'         => 'Server configuration error. Python is not properly configured.',
            'not found'      => 'Server configuration error. Please contact administrator.',
            'not recognized' => 'Server configuration error. Python is not installed or not in PATH.',
        ];

        $lowerError = strtolower($error);

        foreach ($errorMap as $key => $message) {
            if (str_contains($lowerError, $key)) {
                return $message;
            }
        }

        return 'Failed to fetch media. Please check the URL and try again.';
    }
}