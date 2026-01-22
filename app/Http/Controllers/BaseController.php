<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected $sharedData = [];
    
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $user = Auth::user();
                $this->sharedData = [
                    'sidebarCollapsed' => false, // Default value
                    'user' => $user,
                    'connectionCount' => $user->connection_count ?? 0,
                    'journalCount' => $user->journals()->count() ?? 0,
                ];
            }
            
            return $next($request);
        });
    }
    
    protected function shareData($data = [])
    {
        return array_merge($this->sharedData, $data);
    }
}