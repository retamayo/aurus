<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use App\Services\GeminiService;
use Filament\Notifications\Notification;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('aiGenerate')
                ->label('Generate with AI')
                ->icon('heroicon-m-sparkles')
                ->form([
                    TextInput::make('prompt')
                        ->label('Prompt')
                        ->placeholder('A dark fantasy about a cursed child...')
                        ->required(),
                ])
                ->action(function (array $data, $livewire) {
                    $gemini = app(GeminiService::class);

                    $rawJson = $gemini->generate(
                        prompt: "Based on the following prompt, generate a JSON response including: 
                    - title (string),
                    - slug (string), // replace spaces with dashes and lowercase the string
                    - synopsis (string), make it detailed and informative and engaging, present it in a way that would make the reader want to read the book, add line breaks and preserve white spaces and line breaks, must have structured markdown output.
                    - tags (array of strings), 
                    - genre (string from a fixed list), 
                    - notes (string), // Make it detailed and informative, add line breaks and preserve white spaces and line breaks, must have structured markdown output.
                    - and a story_outline (array of chapters with titles and short descriptions).
                    
                    The genre must be one of the following predefined options: Fantasy, Sci-Fi, Romance, Mystery, Non-fiction.
                    
                    Respond only with raw JSON.

                    JSON response format:
                    {
                        'title': 'string',
                        'slug': 'string',
                        'synopsis': 'string',
                        'tags': ['string', 'string'],
                        'genre': 'string',
                        'notes': 'string',
                        'story_outline': [
                            {
                                'chapter_title': 'string',
                                'chapter_description': 'string'
                            }
                        ]
                    }

                    JSON must be valid.
                    
                    Prompt:\n{$data['prompt']}"
                    );
                    
                    $cleanedRawJson = preg_replace('/^```json|\s+```$/', '', $rawJson);

                    $decoded = json_decode($cleanedRawJson, true);

                    // dd($cleanedRawJson);

                    if (!is_array($decoded)) {
                        Notification::make()
                            ->title('Invalid AI response')
                            ->body('The AI did not return valid JSON. Please try again.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $currentState = $livewire->form->getState();

                    $livewire->form->fill([
                        'book_title' => $decoded['title'] ?? $currentState['book_title'] ?? '',
                        'book_slug' => $decoded['slug'] ?? $currentState['book_slug'] ?? '',
                        'book_synopsis' => $decoded['synopsis'] ?? $currentState['book_synopsis'] ?? '',
                        'book_tags' => $decoded['tags'] ?? $currentState['book_tags'] ?? [],
                        'book_genre' => $decoded['genre'] ?? $currentState['book_genre'] ?? 'fantasy',
                        'book_notes' => $decoded['notes']  ?? $currentState['book_notes'] ?? '',
                        'book_ai_context' => json_encode([
                            'outline' => $decoded['story_outline'] ?? [],
                            'source_prompt' => $data['prompt'] ?? '',
                        ]),
                        'book_language' => $currentState['book_language'] ?? $currentState['book_language'] ?? 'English',
                        'book_visibility' => $currentState['book_visibility'] ?? $currentState['book_visibility'] ?? 'public',
                        'book_status' => $currentState['book_status'] ?? $currentState['book_status'] ?? 'draft',
                        'book_is_premium' => $currentState['book_is_premium'] ?? $currentState['book_is_premium']?? false,
                        'book_token_cost' => $currentState['book_token_cost'] ?? $currentState['book_token_cost'] ?? 0,
                    ]);

                    Notification::make()
                        ->title('AI generation complete')
                        ->body('The form has been filled with AI-generated content.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Generate Book with AI')
                ->modalSubmitActionLabel('Generate')
                ->modalWidth('xl'),
        ];
    }
}
