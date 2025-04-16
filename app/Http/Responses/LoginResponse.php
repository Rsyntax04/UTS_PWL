<?php
namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
 
class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return redirect()->to(Dashboard::getUrl(panel: 'admin'));
        }
        elseif($user->hasRole('kaprodi')) {
            return redirect()->to(Dashboard::getUrl(panel:'kaprodi'));
        }
        elseif($user->hasRole('mo')) {
            return redirect()->to(Dashboard::getUrl(panel:'mo'));
        }
        elseif($user->hasRole('mahasiswa')) {
            return redirect()->to(Dashboard::getUrl(panel:'mahasiswa'));
        }
 
        return parent::toResponse($request);
    }
}