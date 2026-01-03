<?php

namespace App\Livewire;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeSwitcher extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'theme' => Auth::user()->theme ?? 'default',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('theme')
                    ->label('')
                    ->options([
                        'default' => 'Default (Blue)',
                        'dracula' => 'Dracula',
                        'nord' => 'Nord',
                        'sunset' => 'Sunset',
                    ])
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(function (string $state) {
                        $user = Auth::user();
                        if ($user) {
                            $user->theme = $state;
                            $user->save();
                            return redirect(request()->header('Referer'));
                        }
                    })
                    ->extraInputAttributes(['class' => '!border-none !shadow-none !bg-transparent !w-40 focus:!ring-0'])
                    ->prefixIcon('heroicon-o-swatch'),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('livewire.theme-switcher');
    }
}
