<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use App\Services\GeminiService;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Support\Components\Stack;
use Filament\Infolists\Components\Section;

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
                        Wizard\Step::make('Story Setup')
                        ->schema([
                            Grid::make()
                            ->schema([
                                Grid::make()
                                ->schema([
                                    TextInput::make('arc_count')
                                    ->label('Arc Count')
                                    ->type('number')
                                    ->minValue(1)
                                    ->maxValue(30)
                                    ->required()
                                    ->default(3)
                                    ->helperText('Set how many arcs the story outline should have'),
                                    TextInput::make('chapter_count')
                                    ->label('Chapter Count')
                                    ->type('number')
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->required()
                                    ->default(10)
                                    ->helperText('Set how many chapters each arc should have'),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
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
                                    ->reactive(),
                    
                                Select::make('genre')
                                    ->label('Genre')
                                    ->options([
                                        'Fantasy' => 'Fantasy',
                                        'Sci-Fi' => 'Sci-Fi',
                                        'Romance' => 'Romance',
                                        'Thriller' => 'Thriller',
                                        'Drama' => 'Drama',
                                        'Adventure' => 'Adventure',
                                        'Mystery' => 'Mystery',
                                        'Horror' => 'Horror',
                                        'Historical' => 'Historical',
                                    ])
                                    ->disabled(fn ($get) => blank($get('tone')))
                                    ->required()
                                    ->reactive(),

                                Select::make('story_format')
                                    ->label('Story Format')
                                    ->options([
                                        'novel' => 'Novel',
                                        'light_novel' => 'Light Novel',
                                        'web_novel' => 'Web Novel',
                                        'litrpg' => 'LitRPG',
                                        'young_adult' => 'Young Adult',
                                        'fan_fiction' => 'Fan Fiction',
                                        'short_story' => 'Short Story',
                                        'novella' => 'Novella',
                                        'graphic_novel' => 'Graphic Novel',
                                        'serial' => 'Serialized Story',
                                        'anthology' => 'Anthology',
                                        'flash_fiction' => 'Flash Fiction',
                                    ])                                    
                                    ->disabled(fn ($get) => blank($get('genre')))
                                    ->required()
                                    ->reactive(),

                                TextInput::make('theme')
                                    ->label('Theme')
                                    ->placeholder('E.g. Redemption, Sacrifice, Coming of Age')
                                    ->reactive()
                                    ->required()
                                    ->disabled(fn ($get) => blank($get('story_format')))
                                    ->suffixAction(
                                            FormAction::make('fillTheme')
                                                ->icon('heroicon-s-puzzle-piece')
                                                ->tooltip('Suggest story theme')
                                                ->action(function (array $arguments, callable $set, callable $get) {
                                                    $tone = $get('tone') ?? 'epic';
                                                    $genre = $get('genre') ?? 'fantasy';
                                                    $story_format = $get('story_format') ?? 'web-novel';
                                                    $gemini = app(GeminiService::class);
                                                    $response = $gemini->generate(
                                                        $prompt = "Suggest an overarching theme for a '$tone' '$genre' story in the format of a '$story_format'. Include twists and turns to keep readers engaged. Don't add extra words or special characters, just the setting description please."
                                                    );
                                                    $set('theme', trim($response));
                                            })
                                            ->disabled(fn ($get) => blank($get('story_format'))),
                                        )
                                    ->columnSpanFull(),

                                TextInput::make('conflict')
                                    ->label('Main Conflict')
                                    ->placeholder('E.g. A forbidden love threatens the balance of realms')
                                    ->reactive()
                                    ->required()
                                    ->disabled(fn ($get) => blank($get('theme')))
                                    ->suffixAction(
                                        FormAction::make('fillConflict')
                                            ->icon('heroicon-s-fire')
                                            ->tooltip('Suggest story conflict')
                                            ->action(function (array $arguments, callable $set, callable $get) {
                                                $tone = $get('tone') ?? 'epic';
                                                $genre = $get('genre') ?? 'fantasy';
                                                $story_format = $get('story_format') ?? 'web-novel';
                                                $theme = $get('theme') ?? 'generic fantasy theme';
                                                $gemini = app(GeminiService::class);
                                                $response = $gemini->generate(
                                                    $prompt = "Suggest a rich and exciting conflict for a '$tone' '$genre' story in the format of a '$story_format', centered around the theme '$theme'. Include twists and turns to keep readers engaged. Don't add extra words or special characters, just the setting description please."
                                                );
                                                $set('conflict', trim($response));
                                            })
                                            ->disabled(fn ($get) => blank($get('theme'))),
                                    )
                                    ->columnSpanFull(),
                                
                                TextInput::make('prompt')
                                    ->label('Prompt')
                                    ->placeholder('A dark fantasy about a cursed child...')
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn ($get) => blank($get('conflict')))
                                    ->suffixAction(
                                        FormAction::make('fillPrompt')
                                            ->icon('heroicon-s-light-bulb')
                                            ->tooltip('Suggest prompt')
                                            ->action(function (array $arguments, callable $set, callable $get) {
                                                $tone = $get('tone') ?? 'epic';
                                                $genre = $get('genre') ?? 'fantasy';
                                                $story_format = $get('story_format') ?? 'web-novel';
                                                $theme = $get('theme') ?? 'generic fantasy theme';
                                                $conflict = $get('conflict') ?? 'generic fantasy conflict';
                                                $gemini = app(GeminiService::class);
                                                $response = $gemini->generate(
                                                    $prompt = "Generate a creative story prompt in a '$tone' tone for a '$genre' story in the format of a '$story_format', centered around the theme '$theme' and involving the conflict '$conflict'. Don't use the tone in the prompt. Don't add extra words or special characters, just the prompt please."
                                                );
                                                $set('prompt', trim($response));
                                            })
                                            ->disabled(fn ($get) => blank($get('conflict'))),
                                    )
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                        ]),
                        Wizard\Step::make('Character & Setting')
                        ->schema([
                            Grid::make()
                        ->schema([
                            TextInput::make('mc_age')
                                ->label('Main Character Age')
                                ->type('number')
                                ->minValue(1)
                                ->maxValue(30)
                                ->required()
                                ->default(3),

                            Select::make('mc_gender')
                                ->label('Main Character Gender')
                                ->options([
                                    'm' => 'Male',
                                    'f' => 'Female',
                                    'nb' => 'Non-binary',
                                    'Unknown' => 'Unknown',
                                ])
                                ->disabled(fn ($get) => blank($get('mc_age'))),

                            TextInput::make('mc_appearance')
                                ->label('Main Character Appearance')
                                ->placeholder('E.g. Redemption, Sacrifice, Coming of Age')
                                ->reactive()
                                ->required()
                                ->columnSpanFull()
                                ->suffixAction(
                                    FormAction::make('fillMainCharacterAppearance')
                                        ->icon('heroicon-s-user')
                                        ->tooltip('Suggest main character appearance')
                                        ->action(function (array $arguments, callable $set, callable $get) {
                                            $tone = $get('tone') ?? 'fantasy';
                                            $mc_age = $get('mc_age') ?? 20;
                                            $mc_gender = $get('mc_gender') ?? 'm';
                                            $prompt = $get('prompt') ?? '';
                                            $genderText = match ($mc_gender) {
                                                'm' => 'male',
                                                'f' => 'female',
                                                'nb' => 'non-binary',
                                                default => 'person',
                                            };
                                            $gemini = app(GeminiService::class);
                                            $response = $gemini->generate(
                                                prompt: "Generate the appearance of the main character in a '$tone' story. The character is a {$mc_age}-year-old {$genderText} Prompt: {$prompt}\nDon't add extra words or special characters, just the character appearance description please."
                                            );
                                            $set('mc_appearance', trim($response));
                                        })
                                )
                                ->disabled(fn ($get) => blank($get('mc_gender'))),

                            TextInput::make('mc_temperament')
                                ->label('Main Character Temperament')
                                ->placeholder('E.g. Redemption, Sacrifice, Coming of Age')
                                ->reactive()
                                ->required()
                                ->columnSpanFull()
                                ->suffixAction(
                                    FormAction::make('fillMainCharacterTemperament')
                                        ->icon('heroicon-s-heart')
                                        ->tooltip('Suggest main character temperament')
                                        ->action(function (array $arguments, callable $set, callable $get) {
                                            $tone = $get('tone') ?? 'fantasy';
                                            $mc_age = $get('mc_age') ?? 20;
                                            $mc_gender = $get('mc_gender') ?? 'm';
                                            $mc_appearance = $get('mc_appearance') ?? 'A generic main character appearance';
                                            $prompt = $get('prompt') ?? '';

                                            $genderText = match ($mc_gender) {
                                                'm' => 'male',
                                                'f' => 'female',
                                                'nb' => 'non-binary',
                                                default => 'person',
                                            };

                                            $gemini = app(GeminiService::class);
                                            $response = $gemini->generate(
                                                prompt: "Generate a unique main character temperament for a '{$tone}' story. The character is a {$mc_age}-year-old {$genderText}. Appearance: {$mc_appearance}. Prompt: {$prompt}\nDon't add extra words or special characters, just the character temperament description please."
                                            );
                                            $set('mc_temperament', trim($response));
                                        })
                                )
                                ->disabled(fn ($get) => blank($get('mc_temperament'))),

                            TextInput::make('mc_backstory')
                                ->label('Main Character Backstory')
                                ->placeholder('E.g. Redemption, Sacrifice, Coming of Age')
                                ->reactive()
                                ->required()
                                ->columnSpanFull()
                                ->suffixAction(
                                    FormAction::make('fillMainCharacterBackstory')
                                        ->icon('heroicon-s-clock')
                                        ->tooltip('Suggest main character backstory')
                                        ->action(function (array $arguments, callable $set, callable $get) {
                                            $tone = $get('tone') ?? 'fantasy';
                                            $mc_age = $get('mc_age') ?? 20;
                                            $mc_gender = $get('mc_gender') ?? 'm';
                                            $mc_appearance = $get('mc_appearance') ?? 'A generic main character appearance';
                                            $mc_temperament = $get('mc_temperament') ?? 'A generic main character appearance';
                                            $prompt = $get('prompt') ?? '';

                                            $genderText = match ($mc_gender) {
                                                'm' => 'male',
                                                'f' => 'female',
                                                'nb' => 'non-binary',
                                                default => 'person',
                                            };

                                            
                                            $gemini = app(GeminiService::class);
                                            $response = $gemini->generate(
                                                prompt: "Generate a unique main character backstory for a '{$tone}' story. The character is a {$mc_age}-year-old {$genderText} with the following traits: Appearance: {$mc_appearance}. Temperament: {$mc_temperament}. Prompt: {$prompt} Don't add extra words or special characters, just the character backstory description please."
                                            );
                                            $set('mc_backstory', trim($response));
                                        })
                                )
                                ->disabled(fn ($get) => blank($get('mc_temperament'))),

                            TextInput::make('mc_goals')
                                ->label('Main Character Goals')
                                ->placeholder('E.g. Redemption, Sacrifice, Coming of Age')
                                ->reactive()
                                ->required()
                                ->columnSpanFull()
                                ->suffixAction(
                                    FormAction::make('fillMainCharacterGoals')
                                        ->icon('heroicon-s-bolt')
                                        ->tooltip('Suggest main character')
                                        ->action(function (array $arguments, callable $set, callable $get) {
                                            $tone = $get('tone') ?? 'fantasy';
                                            $mc_age = $get('mc_age') ?? 20;
                                            $mc_gender = $get('mc_gender') ?? 'm';
                                            $mc_appearance = $get('mc_appearance') ?? 'A generic main character appearance';
                                            $mc_temperament = $get('mc_temperament') ?? 'A generic main character temperament';
                                            $mc_backstory = $get('mc_back_story') ?? 'A generic main character backstory';
                                            $prompt = $get('prompt') ?? '';
                                            $gemini = app(GeminiService::class);
                                            $response = $gemini->generate(
                                                prompt: "Generate a unique main character goal for a '$tone' story. Prompt: {$prompt}\nCharacter Info:\n- Age: {$mc_age}\n- Gender: {$mc_gender}\n- Appearance: {$mc_appearance}\n- Temperament: {$mc_temperament}\n- Backstory: {$mc_backstory}\nDon't add extra words or special characters, just the character goal description please."
                                            );
                                            $set('mc_goals', trim($response));
                                        })
                                )
                                ->disabled(fn ($get) => blank($get('mc_backstory'))),
                
                            TextInput::make('setting')
                                ->label('Setting')
                                ->placeholder('E.g. The fallen city of Nyreth under eternal night')
                                ->reactive()
                                ->required()
                                ->disabled(fn ($get) => blank($get('mc_goals')))
                                ->suffixAction(
                                    FormAction::make('fillSetting')
                                        ->icon('heroicon-s-map')
                                        ->tooltip('Suggest setting')
                                        ->action(function (array $arguments, callable $set, callable $get) {
                                            $tone = $get('tone') ?? 'fantasy';
                                            $mc_age = $get('mc_age') ?? 20;
                                            $mc_gender = $get('mc_gender') ?? 'm';
                                            $mc_appearance = $get('mc_appearance') ?? 'A generic main character appearance';
                                            $mc_temperament = $get('mc_temperament') ?? 'A generic main character temperament';
                                            $mc_backstory = $get('mc_back_story') ?? 'A generic main character backstory';
                                            $mc_goals = $get('mc_goals') ?? 'A generic main character goals';
                                            $prompt = $get('prompt') ?? '';
                                            $genre = $get('genre') ?? 'fantasy';
                                            $story_format = $get('story_format') ?? 'web-novel';
                                            $gemini = app(GeminiService::class);
                                            $response = $gemini->generate(
                                                prompt: "Suggest a rich setting for a '$tone' $genre $story_format involving a {$mc_age}-year-old {$mc_gender} with {$mc_appearance}, a temperament described as '{$mc_temperament}', a backstory: '{$mc_backstory}', and goals: '{$mc_goals}'. Prompt: {$prompt}\nDon't add extra words or special characters, just the setting description please."
                                            );
                                            $set('setting', trim($response));
                                        })
                                )
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    ]),
                    Wizard\Step::make('Audience')
                    ->schema([
                        Grid::make()
                        ->schema([
                            Select::make('audience_gender')
                                ->label('Target Audience Gender')
                                ->options([
                                    'Male' => 'Male',
                                    'Female' => 'Female',
                                    'All' => 'All',
                                ])
                                ->required()
                                ->default('All'),
                
                            Select::make('audience_age_group')
                                ->label('Target Audience Age Group')
                                ->options([
                                    'Children' => 'Children',
                                    'Teens' => 'Teens',
                                    'Young Adults' => 'Young Adults',
                                    'Adults' => 'Adults',
                                ])
                                ->required()
                                ->default('Young Adults'),
                            
                            TagsInput::make('audience_interests')
                                ->label('Target Audience Interests')
                                ->placeholder('E.g. Fantasy, Adventure, Romance')
                                ->reactive()
                                ->disabled(fn ($get) => blank($get('audience_age_group')))
                                ->helperText('Add interests that would appeal to your target audience')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    ]),
                
                ]),
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
                    Genre:\n{$data['genre']}
                    Story Format:\n{$data['story_format']}
                    Theme:\n{$data['theme']}
                    Conflict:\n{$data['conflict']}
                    Main Character Age:\n{$data['mc_age']}
                    Main Character Appearance:\n{$data['mc_appearance']}
                    Main Character Temperament:\n{$data['mc_temperament']}
                    Main Character Backstory:\n{$data['mc_backstory']}
                    Main Character Goals:\n{$data['mc_goals']}
                    Prompt:\n{$data['prompt']}
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
