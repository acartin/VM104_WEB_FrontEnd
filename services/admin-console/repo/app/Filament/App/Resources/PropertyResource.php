<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $tenantOwnershipRelationshipName = 'client';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Properties';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Property Info')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('property_type_id')
                            ->relationship('propertyType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'available' => 'Available',
                                'reserved' => 'Reserved',
                                'sold' => 'Sold',
                                'rented' => 'Rented',
                            ])
                            ->default('available')
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('address_street')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_city')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address_state')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address_zip')
                            ->maxLength(20),
                    ])->columns(2),
                Forms\Components\Section::make('Details & Price')
                    ->schema([
                        Forms\Components\TextInput::make('bedrooms')
                            ->numeric(),
                        Forms\Components\TextInput::make('bathrooms')
                            ->numeric(),
                        Forms\Components\TextInput::make('area_sqm')
                            ->numeric()
                            ->label('Area (sqm)'),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('propertyType.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('address_city')
                    ->label('City')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'sold' => 'danger',
                        'rented' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListProperties::route('/'),
            'view' => Pages\ViewProperty::route('/{record}'),
        ];
    }
}
