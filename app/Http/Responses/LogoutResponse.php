<?php
namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Filament\Http\Responses\Auth\LogoutResponse as BaseLogoutResponse;
 
class LogoutResponse extends BaseLogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        if (Filament::getCurrentPanel()->getId() === 'admin') {
            return redirect()->to('/');
        }elseif(Filament::getCurrentPanel()->getId() === 'kaprodi'){
            return redirect()->to('/');
        }elseif(Filament::getCurrentPanel()->getId() === 'mo'){
            return redirect()->to('/');
        }elseif(Filament::getCurrentPanel()->getId() === 'mahasiswa'){
            return redirect()->to('/');
        }
 
        return parent::toResponse($request);
    }
}