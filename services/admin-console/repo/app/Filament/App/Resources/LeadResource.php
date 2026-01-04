<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $tenantOwnershipRelationshipName = 'client';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Leads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lead Info')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                    ])->columns(2),
                Forms\Components\Section::make('Financial Info')
                    ->schema([
                        Forms\Components\TextInput::make('declared_income')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('current_debts')
                            ->numeric()
                            ->prefix('$'),
                    ])->columns(2),
                Forms\Components\Section::make('Property Reference')
                    ->description('Optional: Link this lead to a specific property listing')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('source_property_ref')
                            ->label('Property Reference ID')
                            ->helperText('External property ID (e.g., wp-12345, mls-abc-789)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_property_url')
                            ->label('Property URL')
                            ->url()
                            ->helperText('Direct link to the property listing')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('estimated_value')
                            ->label('Estimated Property Value')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Captured value at lead creation'),
                        Forms\Components\Textarea::make('property_snapshot')
                            ->label('Property Snapshot (JSON)')
                            ->helperText('Additional property details in JSON format')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Status & source')
                    ->schema([
                        Forms\Components\Select::make('source_id')
                            ->relationship('source', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status_id')
                            ->relationship('leadStatus', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('contact_preference_id')
                            ->label('Contact Preference')
                            ->relationship('contactPreference', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('full_name')
                    ->label('Lead / Score')
                    ->view('filament.app.resources.lead-resource.columns.lead-score')
                    ->searchable(['full_name', 'email'])
                    ->sortable('score_total'),

                Tables\Columns\ViewColumn::make('score_engagement')
                    ->label('Engagement')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->viewData([
                        'type' => 'engagement',
                        'label' => 'Engagement',
                    ]),

                Tables\Columns\ViewColumn::make('score_finance')
                    ->label('Finance')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->viewData([
                        'type' => 'finance',
                        'label' => 'Finance',
                    ]),

                Tables\Columns\TextColumn::make('timelineDef.label')
                    ->label('Timeline')
                    ->alignCenter()
                    ->extraAttributes(fn (Lead $record): array => [
                        'class' => ($record->timelineDef?->color ?? '') . ' t-badge-base',
                    ]),

                Tables\Columns\ViewColumn::make('score_match')
                    ->label('Match')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->viewData([
                        'type' => 'match',
                        'label' => 'Match',
                    ]),

                Tables\Columns\ViewColumn::make('score_info')
                    ->label('Info')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->viewData([
                        'type' => 'info',
                        'label' => 'Data Quality',
                    ]),

                Tables\Columns\IconColumn::make('contactPreference.icon')
                    ->label('Outcome')
                    ->icon(fn ($state) => $state ?? 'heroicon-o-question-mark-circle')
                    ->color(fn (Lead $record) => $record->contactPreference?->color ?? 'gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('leadStatus.name')
                    ->label('Workflow')
                    ->badge()
                    ->color(fn (Lead $record) => $record->leadStatus?->color ?? 'gray')
                    ->icon(fn (Lead $record) => $record->leadStatus?->icon ?? 'heroicon-o-sparkles'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('audit')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->tooltip('Audit Chat & Criteria')
                    ->iconButton()
                    ->color('success')
                    ->url(fn (Lead $record): string => Pages\AuditLead::getUrl(['record' => $record])),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()->color('info'),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Lead $record): string => Pages\AnalysisLead::getUrl(['record' => $record]));
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
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'analysis' => Pages\AnalysisLead::route('/{record}/analysis'),
            'audit' => Pages\AuditLead::route('/{record}/audit'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
            'view' => Pages\ViewLead::route('/{record}'),
        ];
    }
}
