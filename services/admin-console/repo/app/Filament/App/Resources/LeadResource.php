<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Models\ScoringDefinition;
use Illuminate\Support\HtmlString;

/*
 * Tailwind Safelist for Dynamic Colors:
 * text-success-500 text-warning-500 text-danger-500 text-primary-500 text-info-500 text-gray-400
 * text-green-500 text-yellow-500 text-red-500 text-blue-500 text-sky-500
 * bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/30
 * bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-400 dark:ring-yellow-400/30
 * bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30
 * bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30
 * bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-400 dark:ring-sky-400/30
 * bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20
 */
use Illuminate\Support\Facades\Blade;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $tenantOwnershipRelationshipName = 'client';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Leads';

    public static function renderIcon(?string $icon, ?string $color = null, string $class = 'w-4 h-4'): HtmlString
    {
        if (!$icon) return new HtmlString('');
        
        // Detectar si el color es hex o clase CSS
        $isHex = $color && str_starts_with($color, '#');
        
        if ($isHex) {
            // Usar estilo inline para hex
            return new HtmlString("<span style='color: {$color};'>
                <i data-lucide='{$icon}' class='{$class}'></i>
            </span>");
        } else {
            // Fallback a clases CSS (para thermal-* y otros)
            $colorClass = $color ?? 'text-gray-400';
            return new HtmlString("<span class='{$colorClass}'>
                <i data-lucide='{$icon}' class='{$class}'></i>
            </span>");
        }
    }

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
                Tables\Columns\ViewColumn::make('score_total')
                    ->label('Lead / Score')
                    ->view('filament.app.resources.lead-resource.columns.lead-score')
                    ->searchable(['full_name', 'email'])
                    ->sortable(),

                Tables\Columns\ViewColumn::make('score_engagement')
                    ->label('Engagement')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->sortable()
                    ->viewData([
                        'type' => 'engagement',
                        'label' => 'Engagement',
                    ]),

                Tables\Columns\ViewColumn::make('score_finance')
                    ->label('Finance')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->sortable()
                    ->viewData([
                        'type' => 'finance',
                        'label' => 'Finance',
                    ]),

                Tables\Columns\TextColumn::make('score_timeline')
                    ->label('Timeline')
                    ->formatStateUsing(fn (Lead $record): string => $record->timelineDef?->label ?? '-')
                    ->alignCenter()
                    ->sortable()
                    ->extraAttributes(fn (Lead $record): array => [
                        'class' => ($record->timelineDef?->color ?? '') . ' t-badge-base',
                    ]),

                Tables\Columns\ViewColumn::make('score_match')
                    ->label('Match')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->sortable()
                    ->viewData([
                        'type' => 'match',
                        'label' => 'Match',
                    ]),

                Tables\Columns\ViewColumn::make('score_info')
                    ->label('Info')
                    ->view('filament.app.resources.lead-resource.columns.thermal-icon')
                    ->alignCenter()
                    ->sortable()
                    ->viewData([
                        'type' => 'info',
                        'label' => 'Data Quality',
                    ]),

                Tables\Columns\ViewColumn::make('contactPreference.name')
                    ->label('Outcome')
                    ->view('filament.app.resources.lead-resource.columns.icon-viewer')
                    ->viewData(fn (Lead $record) => [
                        'icon' => $record->contactPreference?->icon ?? 'help-circle',
                        'color' => $record->contactPreference?->color ?? '#9ca3af',
                    ])
                    ->alignCenter(),

                Tables\Columns\ViewColumn::make('leadStatus.name')
                    ->label('Workflow')
                    ->view('filament.app.resources.lead-resource.columns.icon-viewer')
                    ->viewData(fn (Lead $record) => [
                        'icon' => $record->leadStatus?->icon ?? 'sparkles',
                        'color' => $record->leadStatus?->color ?? '#9ca3af',
                    ])
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('score_total', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with([
                'leadStatus',
                'contactPreference',
                'source',
                'engagementDef',
                'financeDef',
                'timelineDef',
                'matchDef',
                'infoDef',
                'priorityDef',
            ]))
            ->filters([
                Tables\Filters\Filter::make('status_filter')
                    ->form([
                        Forms\Components\CheckboxList::make('status_ids')
                            ->label('Workflow Status')
                            ->bulkToggleable()
                            ->options(fn() => \Illuminate\Support\Facades\Cache::remember('filter_lead_statuses', 3600, fn() => 
                                \App\Models\LeadStatus::orderBy('order')->get()->mapWithKeys(fn($s) => [
                                    $s->id => new HtmlString(
                                        "<div class='flex items-center gap-2'>" .
                                        static::renderIcon($s->icon, $s->color, 'w-4 h-4') .
                                        "<span>{$s->name}</span>
                                        </div>"
                                    )
                                ])
                            )),
                    ])
                    ->query(fn ($query, array $data) => $query->when($data['status_ids'], fn($q) => $q->whereIn('status_id', $data['status_ids'])))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['status_ids'] ?? null)) return null;
                        $count = count($data['status_ids']);
                        $names = \App\Models\LeadStatus::whereIn('id', $data['status_ids'])->pluck('name')->join(', ');
                        return "Workflow: {$names}";
                    }),

                Tables\Filters\Filter::make('intent_filter')
                    ->form([
                        Forms\Components\CheckboxList::make('intent_ids')
                            ->label('Lead Intent (Outcome)')
                            ->bulkToggleable()
                            ->options(fn() => \App\Models\ContactPreference::all()->mapWithKeys(fn($p) => [
                                $p->id => new HtmlString(
                                    "<div class='flex items-center gap-2'>" .
                                    static::renderIcon($p->icon, $p->color, 'w-4 h-4') .
                                    "<span>{$p->name}</span></div>"
                                )
                            ])),
                    ])
                    ->query(fn ($query, array $data) => $query->when($data['intent_ids'], fn($q) => $q->whereIn('contact_preference_id', $data['intent_ids'])))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['intent_ids'] ?? null)) return null;
                        $names = \App\Models\ContactPreference::whereIn('id', $data['intent_ids'])->pluck('name')->join(', ');
                        return "Intent: {$names}";
                    }),

                Tables\Filters\Filter::make('finance_filter')
                    ->form([
                        Forms\Components\CheckboxList::make('finance_ids')
                            ->label('Financial Profile')
                            ->bulkToggleable()
                            ->options(fn() => ScoringDefinition::where('criterion', 'finance')->get()->mapWithKeys(fn($s) => [
                                $s->id => new HtmlString(
                                    "<div class='flex items-center gap-2 w-full'>" .
                                    static::renderIcon($s->icon, $s->color, 'w-5 h-5') .
                                    "<span>{$s->label}</span></div>"
                                )
                            ])),
                    ])
                    ->query(fn ($query, array $data) => $query->when($data['finance_ids'], fn($q) => $q->whereIn('fin_def_id', $data['finance_ids'])))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['finance_ids'] ?? null)) return null;
                        $labels = ScoringDefinition::whereIn('id', $data['finance_ids'])->pluck('label')->join(', ');
                        return "Finance: {$labels}";
                    }),

                Tables\Filters\Filter::make('timeline_filter')
                    ->form([
                        Forms\Components\CheckboxList::make('timeline_ids')
                            ->label('Timeline Urgency')
                            ->bulkToggleable()
                            ->options(fn() => ScoringDefinition::where('criterion', 'timeline')->get()->mapWithKeys(fn($s) => [
                                $s->id => new HtmlString(
                                    "<div class='flex items-center gap-2 w-full'>" .
                                    static::renderIcon($s->icon, $s->color, 'w-5 h-5') .
                                    "<span>{$s->label}</span></div>"
                                )
                            ])),
                    ])
                    ->query(fn ($query, array $data) => $query->when($data['timeline_ids'], fn($q) => $q->whereIn('timeline_def_id', $data['timeline_ids'])))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['timeline_ids'] ?? null)) return null;
                        $labels = ScoringDefinition::whereIn('id', $data['timeline_ids'])->pluck('label')->join(', ');
                        return "Timeline: {$labels}";
                    }),

                Tables\Filters\Filter::make('engagement_filter')
                    ->form([
                        Forms\Components\CheckboxList::make('engagement_ids')
                            ->label('Engagement Level')
                            ->bulkToggleable()
                            ->options(fn() => ScoringDefinition::where('criterion', 'engagement')->get()->mapWithKeys(fn($s) => [
                                $s->id => new HtmlString(
                                    "<div class='flex items-center gap-2 w-full'>" .
                                    static::renderIcon($s->icon, $s->color, 'w-5 h-5') .
                                    "<span>{$s->label}</span></div>"
                                )
                            ])),
                    ])
                    ->query(fn ($query, array $data) => $query->when($data['engagement_ids'], fn($q) => $q->whereIn('eng_def_id', $data['engagement_ids'])))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['engagement_ids'] ?? null)) return null;
                        $labels = ScoringDefinition::whereIn('id', $data['engagement_ids'])->pluck('label')->join(', ');
                        return "Engagement: {$labels}";
                    }),

                Tables\Filters\Filter::make('score_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('min_score')
                                    ->numeric()
                                    ->label('Min Score')
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('max_score')
                                    ->numeric()
                                    ->label('Max Score')
                                    ->placeholder('100'),
                            ]),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_score'], fn ($q) => $q->where('score_total', '>=', $data['min_score']))
                            ->when($data['max_score'], fn ($q) => $q->where('score_total', '<=', $data['max_score']));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['min_score'] && !$data['max_score']) {
                            return null;
                        }
                        return 'Score: ' . ($data['min_score'] ?? 0) . ' - ' . ($data['max_score'] ?? 100);
                    }),
            ], layout: Tables\Enums\FiltersLayout::Modal)
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->icon('heroicon-m-funnel')
                    ->slideOver(),
            )
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
            ->deferLoading()
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
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
