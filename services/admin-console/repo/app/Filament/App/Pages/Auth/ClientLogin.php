<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Pages\Auth\Login;

class ClientLogin extends Login
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'cocacolito@admin.com',
            'password' => 'password',
        ]);
    }
}
