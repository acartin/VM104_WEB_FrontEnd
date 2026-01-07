<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash; 

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $tenantOwnershipRelationshipName = 'clients';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Security';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Profile')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('job_title')
                            ->label('Job Title')
                            ->maxLength(100),
                        Forms\Components\Select::make('available_status')
                            ->options([
                                'available' => 'Available',
                                'busy' => 'Busy',
                                'away' => 'Away',
                                'offline' => 'Offline',
                            ])
                            ->default('offline')
                            ->required(),
                        Forms\Components\Toggle::make('can_receive_leads')
                            ->label('Can Receive Leads')
                            ->default(false),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])
                    ->columns(2),



                Forms\Components\Section::make('Contact Methods')
                    ->schema([
                        Forms\Components\Repeater::make('contactMethods')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('channel_id')
                                    ->label('Channel')
                                    ->options(function () {
                                        return \App\Models\CommunicationChannel::where('active', true)
                                            ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('value', null))
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Contact Detail')
                                    ->placeholder('e.g. +1 234 567 8900')
                                    ->prefixIcon(fn (Forms\Get $get) => \App\Models\CommunicationChannel::find($get('channel_id'))?->icon ?? 'heroicon-o-chat-bubble-bottom-center-text')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('label')
                                    ->placeholder('e.g. Work')
                                    ->maxLength(50)
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_primary')
                                    ->label('Primary')
                                    ->default(false)
                                    ->columnSpan(1)
                                    ->inline(false),
                                \Filament\Forms\Components\Actions::make([
                                    \Filament\Forms\Components\Actions\Action::make('delete')
                                        ->iconButton()
                                        ->icon('heroicon-m-trash')
                                        ->color('danger')
                                        ->action(function ($component) {
                                            $item = $component->getContainer();
                                            $repeater = $item->getParentComponent();
                                            $state = $repeater->getState();
                                            $uuid = $item->getName();
                                            unset($state[$uuid]);
                                            $repeater->state($state);
                                        })
                                ])->columnSpan(1),
                            ])
                            ->columns(6)
                            ->reorderable(false)
                            ->deletable(false)
                            ->addActionLabel('Add Channel')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'client' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('can_receive_leads')
                    ->label('Receives Leads')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('available_status')
                    ->label('Estado')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'available' => 'heroicon-m-check-circle',
                        'busy' => 'heroicon-m-minus-circle',
                        'away' => 'heroicon-m-clock',
                        'offline' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'busy' => 'danger',
                        'away' => 'warning',
                        'offline' => 'gray',
                        default => 'gray',
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
