<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\KnowledgeDocumentResource\Pages;
use App\Models\LeadKnowledgeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class KnowledgeDocumentResource extends Resource
{
    protected static ?string $model = LeadKnowledgeDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Knowledge Base';

    protected static ?string $tenantRelationshipName = 'leadKnowledgeDocuments';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'lead_admin', 'client_admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('storage_path')
                    ->label('Document (PDF)')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('local')
                    ->directory(fn () => 'documents/' . Filament::getTenant()->id)
                    ->storeFileNamesIn('filename')
                    ->preserveFilenames()
                    ->visibility('private')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filename')
                    ->searchable()
                    ->label('File Name'),
                
                Tables\Columns\TextColumn::make('sync_status')
                    ->badge()
                    ->colors([
                        'gray' => 'PENDING',
                        'success' => 'SYNCED',
                        'danger' => 'FAILED',
                    ])
                    ->tooltip(fn (LeadKnowledgeDocument $record): ?string => $record->error_message),

                Tables\Columns\TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('retry_sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->iconButton()
                    ->color('primary')
                    ->visible(fn (LeadKnowledgeDocument $record) => $record->sync_status !== 'SYNCED')
                    ->action(function (LeadKnowledgeDocument $record) {
                        $record->sync_status = 'PENDING';
                        $record->save();
                        \App\Jobs\IngestDocumentJob::dispatch($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Sync job dispatched.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKnowledgeDocuments::route('/'),
        ];
    }
}
