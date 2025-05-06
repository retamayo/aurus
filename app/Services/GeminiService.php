<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;

class GeminiService
{
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function generate(string $prompt, array $context = []): string
    {
        try {
            $response = Http::post($this->endpoint . '?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $this->buildContextPrompt($prompt, $context)],
                        ],
                    ],
                ],
            ]);
    
            return $response->json('candidates.0.content.parts.0.text') ?? 'No response';
        } catch (\Throwable $th) {
            return 'An error occurred while connecting to Gemini.';
        }
    }

    protected function buildContextPrompt(string $prompt, array $context): string
    {
        $contextText = implode("\n", $context);
        return "Context:\n$contextText\n\nUser Prompt:\n$prompt";
    }
}
