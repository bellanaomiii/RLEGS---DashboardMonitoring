<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Divisi;
use App\Models\AccountManager;

class DivisiLeaderboardController extends Controller
{
    public function index($divisi_id)
    {
        $divisi = Divisi::findOrFail($divisi_id);

        $accountManagers = AccountManager::where('divisi_id', $divisi_id)
            ->with(['witel', 'divisi', 'revenues'])
            ->get()
            ->map(function ($am) {
                $totalRevenue = $am->revenues->sum('real_revenue');
                $totalTarget = $am->revenues->sum('target_revenue');
                $achievement = $totalTarget > 0 ? ($totalRevenue / $totalTarget * 100) : 0;

                return [
                    'id' => $am->id,
                    'nama' => $am->nama,
                    'nik' => $am->nik,
                    'witel' => $am->witel->nama ?? 'N/A',
                    'divisi' => $am->divisi->nama ?? 'N/A',
                    'total_revenue' => $totalRevenue,
                    'total_target' => $totalTarget,
                    'achievement' => $achievement
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        return view('divisi.leaderboard', [
            'divisi' => $divisi,
            'accountManagers' => $accountManagers
        ]);
    }
}