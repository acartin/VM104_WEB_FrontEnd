<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeDocumentResource\Pages;
use App\Models\LeadKnowledgeDocument;
use App\Models\Client;
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

    // No tenantRelationshipName for Admin Panel usage (Global Access)

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'lead_admin']); // Client Admin uses the App Panel version
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Client')
                    ->options(Client::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('storage_path')
                    ->label('Document (PDF)')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('local')
                    // Dynamic directory based on selected client (needs get/set state magic or easier: just allow base documents folder or use a callback)
                    // For Admin upload, knowing the ID purely from form state is tricky before save. 
                    // Simpler approach: Store in 'documents/{client_id}' using $get helper.
                    ->directory(fn (Forms\Get $get) => 'documents/' . ($get('client_id') ?? 'temp'))
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
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->sortable()
                    ->searchable(),
                
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
