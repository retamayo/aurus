<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    protected string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function generate(string $prompt, array $context = []): string
    {
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
    }

    protected function buildContextPrompt(string $prompt, array $context): string
    {
        $contextText = implode("\n", $context);
        return "Context:\n$contextText\n\nUser Prompt:\n$prompt";
    }
}
