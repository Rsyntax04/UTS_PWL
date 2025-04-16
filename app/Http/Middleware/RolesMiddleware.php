<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Pages\Dashboard;
use Spatie\Permission\Models\Role;

class RolesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->to(Dashboard::getUrl(panel: 'admin'));
        } elseif ($user->hasRole('kaprodi')) {
            return redirect()->to(Dashboard::getUrl(panel: 'kaprodi'));
        } elseif ($user->hasRole('dosen')) {
            return redirect()->to(Dashboard::getUrl(panel: 'mo'));
        } elseif ($user->hasRole('mahasiswa')) {
            return redirect()->to(Dashboard::getUrl(panel: 'mahasiswa'));
        }

        return $next($request);
    }
}
