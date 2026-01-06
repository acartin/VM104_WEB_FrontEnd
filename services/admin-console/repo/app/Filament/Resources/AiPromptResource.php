<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiPromptResource\Pages;
use App\Models\AiPrompt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiPromptResource extends Resource
{
    protected static ?string $model = AiPrompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Settings'; // Or 'Configuration' depending on others
    protected static ?string $navigationLabel = 'AI Prompts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('client_id')
                                    ->relationship('client', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->helperText('Leave empty for Global prompts'),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Identificador clave usado por el sistema para encontrar este prompt específico.'),
                                Forms\Components\Toggle::make('is_active')
                                    ->required()
                                    ->default(true)
                                    ->onColor('success'),
                            ])->columns(2),
                        
                        Forms\Components\Section::make('Prompt Content')
                            ->schema([
                                Forms\Components\Textarea::make('prompt_text')
                                    ->label('System Prompt')
                                    ->required()
                                    ->columnSpanFull()
                                    ->rows(10)
                                    ->helperText('Variables available: {context_text}, {input}'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->placeholder('Global')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client')
                    ->relationship('client', 'name'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiPrompts::route('/'),
            'create' => Pages\CreateAiPrompt::route('/create'),
            'edit' => Pages\EditAiPrompt::route('/{record}/edit'),
        ];
    }
}
