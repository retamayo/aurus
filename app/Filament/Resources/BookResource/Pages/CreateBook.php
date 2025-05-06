<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use App\Services\GeminiService;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Select;

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
                    Wizard::make([
                        Wizard\Step::make('Step 1: Tone')
                            ->schema([
                                Select::make('tone')
                                    ->label('Tone')
                                    ->options([
                                        'Dark' => 'Dark',
                                        'Lighthearted' => 'Lighthearted',
                                        'Whimsical' => 'Whimsical',
                                        'Serious' => 'Serious',
                                        'Hopeful' => 'Hopeful',
                                        'Tragic' => 'Tragic',
                                        'Suspenseful' => 'Suspenseful',
                                        'Romantic' => 'Romantic',
                                        'Epic' => 'Epic',
                                        'Comedic' => 'Comedic',
                                        'Philosophical' => 'Philosophical',
                                        'Melancholic' => 'Melancholic',
                                    ])
                                    ->required()
                            ]),
        
                        Wizard\Step::make('Step 2: Prompt')
                            ->schema([
                                TextInput::make('prompt')
                                    ->label('Prompt')
                                    ->placeholder('A dark fantasy about a cursed child...')
                                    ->required()
                                    ->suffixAction(
                                        FormAction::make('fillPrompt')
                                            ->icon('heroicon-s-light-bulb')
                                            ->tooltip('Suggest prompt')
                                            ->action(function (array $arguments, callable $set, callable $get) {
                                                $tone = $get('tone') ?? 'fantasy';
                                                $gemini = app(GeminiService::class);
                                                $response = $gemini->generate(
                                                    prompt: "Generate a creative story prompt in a '$tone' tone. Don't use the tone in the prompt. Don't add extra words or special chracters, Just the prompt please."
                                                );
                                                $set('prompt', trim($response));
                                            })
                                    ),
                            ]),
        
                        Wizard\Step::make('Step 3: Main Character')
                            ->schema([
                                TextInput::make('main_character')
                                    ->label('Main Character')
                                    ->placeholder('E.g. Elira, the cursed princess')
                                    ->suffixAction(
                                        FormAction::make('fillMainCharacter')
                                            ->icon('heroicon-s-user')
                                            ->tooltip('Suggest main character')
                                            ->action(function (array $arguments, callable $set, callable $get) {
                                                $tone = $get('tone') ?? 'fantasy';
                                                $prompt = $get('prompt') ?? '';
                                                $gemini = app(GeminiService::class);
                                                $response = $gemini->generate(
                                                    prompt: "Generate a unique main character name for a '$tone' story. Prompt: {$prompt} \n Don't add extra words or special characters, just the character name, background and appearance description please."
                                                );
                                                $set('main_character', trim($response));
                                            })
                                    ),
                            ]),
        
                        Wizard\Step::make('Step 4: Setting')
                            ->schema([
                                TextInput::make('setting')
                                    ->label('Setting')
                                    ->placeholder('E.g. The fallen city of Nyreth under eternal night')
                                    ->suffixAction(
                                        FormAction::make('fillSetting')
                                            ->icon('heroicon-s-map')
                                            ->tooltip('Suggest setting')
                                            ->action(function (array $arguments, callable $set, callable $get) {
                                                $tone = $get('tone') ?? 'fantasy';
                                                $prompt = $get('prompt') ?? '';
                                                $character = $get('main_character') ?? 'the protagonist';
                                                $gemini = app(GeminiService::class);
                                                $response = $gemini->generate(
                                                    prompt: "Suggest a rich setting for a '$tone' story involving $character. Prompt: {$prompt} \n Don't add extra words or special characters, just the setting description please."
                                                );
                                                $set('setting', trim($response));
                                            })
                                    ),
                            ]),
        
                        Wizard\Step::make('Step 5: Chapter Count')
                            ->schema([
                                TextInput::make('chapter_count')
                                    ->label('Chapter Count')
                                    ->type('number')
                                    ->minValue(1)
                                    ->maxValue(30)
                                    ->default(10)
                                    ->helperText('Set how many chapters the story outline should have'),
                            ]),
                    ])
                ])
                ->action(function (array $data, $livewire) {
                    $gemini = app(GeminiService::class);

                    $rawJson = $gemini->generate(
                        prompt: "Based on the following prompt, generate a JSON response including: 
                    - title (string),
                    - slug (string), // replace spaces with dashes and lowercase the string and remove special characters.
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
                    
                    Tone:\n{$data['tone']}
                    Prompt:\n{$data['prompt']}
                    Main Character:\n{$data['main_character']}
                    Setting:\n{$data['setting']}
                    Chapter Count:\n{$data['chapter_count']}"

                    );
                    
                    $cleanedRawJson = preg_replace('/^```json|\s+```$/', '', $rawJson);

                    
                    $decoded = json_decode($cleanedRawJson, true);

                    if (!is_array($decoded)) {
                        Notification::make()
                            ->title('An error occured.')
                            ->body('There was an error while generating book details. Please try again.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $state = $this->form->getRawState();

                    $livewire->form->fill([
                        'book_title' => $decoded['title'] ?? $state['book_title'] ?? '',
                        'book_slug' => $decoded['slug'] ?? $state['book_slug'] ?? '',
                        'book_synopsis' => $decoded['synopsis'] ?? $state['book_synopsis'] ?? '',
                        'book_tags' => $decoded['tags'] ?? $state['book_tags'] ?? [],
                        'book_genre' => $decoded['genre'] ?? $state['book_genre'] ?? 'fantasy',
                        'book_notes' => $decoded['notes']  ?? $state['book_notes'] ?? '',
                        'book_ai_context' => json_encode([
                            'outline' => $decoded['story_outline'] ?? [],
                            'source_prompt' => $data['prompt'] ?? '',
                        ], JSON_PRETTY_PRINT),
                        'book_language' => $state['book_language'] ?? 'English',
                        'book_visibility' => $state['book_visibility'] ??'public',
                        'book_status' => $state['book_status'] ?? 'draft',
                        'book_is_premium' => $state['book_is_premium'] ?? false,
                        'book_token_cost' => $state['book_token_cost'] ?? 0,
                    ]);

                    Notification::make()
                        ->title('AI generation complete')
                        ->body('The form has been filled with AI-generated content.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Generate Book with AI')
                ->modalSubmitActionLabel('Generate')
                ->modalWidth('2xl'),
            Action::make('checkOriginality')
                ->label('AI Originality Check')
                ->icon('heroicon-s-shield-check')
                ->color('warning')
                ->tooltip('Check if the synopsis might resemble existing works')
                ->action(function (array $arguments, $livewire) {
                    $state = $this->form->getRawState();

                    // dd($state);
                    $synopsis = $state['book_synopsis'] ?? null;
    
                    if (blank($synopsis)) {
                        Notification::make()
                            ->title('Synopsis is empty.')
                            ->danger()
                            ->send();
                        return;
                    }
    
                    $gemini = app(GeminiService::class);
    
                    $check = $gemini->generate(
                        prompt: <<<EOT
                        Evaluate the following book synopsis for originality. Does it appear too similar to any famous or known books? If yes, explain how. If not, confirm its uniqueness.
                        
                        Only return an honest and clear explanation without adding extra formatting.

                        Research it thouroughly.
                        
                        Synopsis:
                        {$synopsis}
                        EOT
                                        );
                        
                                        Notification::make()
                                            ->title('Originality Check Result')
                                            ->body($check)
                                            ->info()
                                            ->persistent()
                                            ->send();
                                    }),
                    ];

                    $originalityMessage = "[Originality Check] Originality check result:\n\n{$check}";

                    $notes = $state['book_notes'];

                    if (strpos($notes, '[Originality Check]') !== false) {
                        $notes = preg_replace('/\[Originality Check\].*?Originality check result:/s', "[Originality Check] Originality check result:\n\n{$check}", $notes);
                    } else if ($originalityMessage == null || $originalityMessage == '') {
                        $notes = null;
                    } else {
                        // Otherwise, append the check result to the notes
                        $notes .= "\n\n" . $originalityMessage;
                    }

                    $livewire->form->fill([
                        'book_title' => $state['book_title'] ?? '',
                        'book_slug' => $state['book_slug'] ?? '',
                        'book_synopsis' => $state['book_synopsis'] ?? '',
                        'book_tags' => $state['book_tags'] ?? [],
                        'book_genre' => $state['book_genre'] ?? 'fantasy',
                        'book_notes' => $notes ?? $state['book_notes'] ?? '',
                        'book_ai_context' => $state['book_ai_context'] ?? '',
                        'book_language' => $state['book_language'] ?? 'English',
                        'book_visibility' => $state['book_visibility'] ??'public',
                        'book_status' => $state['book_status'] ?? 'draft',
                        'book_is_premium' => $state['book_is_premium'] ?? false,
                        'book_token_cost' => $state['book_token_cost'] ?? 0,
                    ]);
    }
}
