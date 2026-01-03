<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScoringDefinitionResource\Pages;
use App\Filament\Resources\ScoringDefinitionResource\RelationManagers;
use App\Models\ScoringDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScoringDefinitionResource extends Resource
{
    protected static ?string $model = ScoringDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('criterion')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('min_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('max_score')
                    ->required()
                    ->numeric()
                    ->default(100),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('meaning')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                Forms\Components\TextInput::make('color')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('criterion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('color')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScoringDefinitions::route('/'),
            'create' => Pages\CreateScoringDefinition::route('/create'),
            'edit' => Pages\EditScoringDefinition::route('/{record}/edit'),
        ];
    }
}
