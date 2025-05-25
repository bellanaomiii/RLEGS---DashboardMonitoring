<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountManager;
use App\Models\Revenue;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterBy = $request->input('filter_by', []);

        // Query menggunakan subquery untuk menghindari masalah dengan GROUP BY
        $query = AccountManager::with(['witel', 'divisi'])
            ->select('account_managers.*')
            ->selectSub(function ($query) {
                $query->selectRaw('SUM(revenues.real_revenue)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id');
            }, 'total_real_revenue')
            ->selectSub(function ($query) {
                $query->selectRaw('SUM(revenues.target_revenue)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id');
            }, 'total_target_revenue')
            ->selectSub(function ($query) {
                $query->selectRaw('(SUM(revenues.real_revenue) / SUM(revenues.target_revenue) * 100)')
                    ->from('revenues')
                    ->whereColumn('revenues.account_manager_id', 'account_managers.id');
            }, 'achievement_percentage');

        // Apply search if provided
        if ($search) {
            $query->where('account_managers.nama', 'like', '%' . $search . '%');
        }

        // Apply filters if provided
        if (in_array('Revenue Realisasi Tertinggi', $filterBy)) {
            $query->orderByDesc('total_real_revenue');
        } elseif (in_array('Achievement Tertinggi', $filterBy)) {
            $query->orderByDesc('achievement_percentage');
        } else {
            // Default sorting by total_real_revenue
            $query->orderByDesc('total_real_revenue');
        }

        $accountManagers = $query->get();

        return view('leaderboardAM', compact('accountManagers'));
    }
}
