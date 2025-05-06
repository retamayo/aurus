<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Wallo\FilamentSelectify\Components\ToggleButton;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Grid as LayoutGrid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Grid::make()->schema([

                Section::make('Book Info')
                    ->schema([
                        Forms\Components\TextInput::make('book_title')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\MarkdownEditor::make('book_synopsis')
                            ->columnSpanFull()
                            ->extraAttributes([
                                "style" => "height: 420px;",
                            ]),
                        
                        Forms\Components\MarkdownEditor::make('book_notes')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan([
                        'md' => 12,
                        'lg' => 9,
                    ]),

                Section::make('Book Options')
                    ->schema([
                        Forms\Components\FileUpload::make('book_cover_image')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('150'),

                        Forms\Components\TextInput::make('book_slug')
                            ->required(),

                        Forms\Components\TagsInput::make('book_tags'),

                        Forms\Components\Select::make('book_genre')
                            ->searchable()
                            ->options([
                                'fantasy' => 'Fantasy',
                                'sci-fi' => 'Sci-Fi',
                                'romance' => 'Romance',
                                'mystery' => 'Mystery',
                                'non-fiction' => 'Non-fiction',
                            ])
                            ->default('fantasy'),

                        Forms\Components\Select::make('book_language')
                            ->options([
                                'English' => 'English',
                                'Spanish' => 'Spanish',
                                'French' => 'French',
                                'German' => 'German',
                            ])
                            ->default('en')
                            ->required(),

                        Forms\Components\Select::make('book_visibility')
                            ->options([
                                'public' => 'Public',
                                'private' => 'Private',
                                'unlisted' => 'Unlisted',
                            ])
                            ->default('public')
                            ->required(),

                        Forms\Components\Select::make('book_status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'In Review',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                        
                        ToggleButton::make('book_is_premium')
                            ->label('Premium')
                            ->onColor('primary')
                            ->offColor('danger')
                            ->offLabel('No')
                            ->onLabel('Yes')
                            ->default(false),

                        Forms\Components\TextInput::make('book_token_cost')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('tokens'),
                    ])
                    ->columnSpan([
                        'md' => 12,
                        'lg' => 3,
                    ]),
            ])
            ->columns(12),

            Section::make('AI Context')
            ->collapsible()
            ->collapsed()
            ->schema([
                Forms\Components\Textarea::make('book_ai_context')
                    ->label('AI Context (for generation)')
                    ->rows(12),
            ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Grid::make()
                    ->columns(1)
                    ->schema([
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('book_genre')
                                ->label('Genre') // Add a clear label
                                ->badge() // Use badge for visual distinction
                                ->icon('heroicon-s-book-open') // Icon for genre
                                ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                            Tables\Columns\ImageColumn::make('book_cover_image')
                                ->height(180)
                                ->extraImgAttributes(['class' => 'rounded-md w-full my-2'])
                                // ->default('https://picsum.photos/seed/'. sha1(rand(1, 99)) .'/800/400'),
                                ->getStateUsing(function ($record, $livewire) {
                                    $index = array_search($record->getKey(), array_keys($livewire->getTableRecords()->pluck('id', 'id')->toArray()));
                                    return $record->project_cover_image ?? 'https://picsum.photos/seed/' . $index + rand(1,99) . '/800/400';
                                }),
                            Tables\Columns\TextColumn::make('book_title')
                                ->searchable()
                                ->weight(FontWeight::Medium),
                            Tables\Columns\TextColumn::make('book_synopsis')
                                ->limit(50)
                                ->html()
                                ->extraAttributes(['class' => 'text-sm text-gray-500']),
                        ]),
                    ]),
                               
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 3,
            ])
            ->filters([
                //
            ])
            ->actions([
                // Action::make('Manage Chapters')
                // ->color('success')
                // ->label('')
                // ->icon('heroicon-m-book-open')
                // ->url(
                //     fn (Project $record): string => static::getUrl('chapters.index', [
                //         'parent' => $record->id,
                //     ])
                // )
                // ->tooltip('Manage Chapters')
                // ->extraAttributes([
                //     'class' => 'rounded-lg ml-auto'
                // ]),
                Tables\Actions\EditAction::make()
                ->extraAttributes([
                    'class' => 'rounded-lg ml-auto'
                ])
                ->tooltip('Edit Project')
                ->label(''),
                Tables\Actions\DeleteAction::make()
                ->extraAttributes([
                    'class' => 'rounded-lg mr-auto'
                ])
                ->tooltip('Delete Project')
                ->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->selectable(false);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view' => Pages\ViewBook::route('/{record}'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
