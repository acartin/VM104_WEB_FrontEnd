<?php

namespace App\Livewire;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserStatusSelector extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'status' => Auth::user()->available_status ?? 'offline',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('status')
                    ->label('')
                    ->options([
                        'available' => 'Available',
                        'busy' => 'Busy',
                        'away' => 'Away',
                        'offline' => 'Offline',
                    ])
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(function (string $state) {
                        $user = Auth::user();
                        $user->available_status = $state;
                        $user->save();
                        
                        $this->dispatch('status-updated');
                    })
                    ->extraInputAttributes(['class' => '!border-none !shadow-none !bg-transparent !w-32 focus:!ring-0'])
                    ->prefixIcon(fn ($state) => match ($state) {
                        'available' => 'heroicon-m-check-circle',
                        'busy' => 'heroicon-m-minus-circle',
                        'away' => 'heroicon-m-clock',
                        'offline' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->prefixIconColor(fn ($state) => match ($state) {
                        'available' => 'success',
                        'busy' => 'danger',
                        'away' => 'warning',
                        'offline' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.user-status-selector');
    }
}
