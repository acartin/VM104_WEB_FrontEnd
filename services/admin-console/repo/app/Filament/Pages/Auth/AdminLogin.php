<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login;

class AdminLogin extends Login
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);
    }

    protected function getAuthenticatedRedirectUrl(): ?string
    {
        return $this->getPanel()->getUrl();
    }
}
