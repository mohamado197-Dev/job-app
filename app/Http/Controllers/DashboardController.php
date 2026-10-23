<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ilike على PostgreSQL، و like على MariaDB (محلياً)
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = JobVacancy::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search, $like) {
                $q->where('title', $like, "%{$search}%")
                    ->orWhere('location', $like, "%{$search}%")
                    ->orWhereHas('company', function ($c) use ($search, $like) {
                        $c->where('name', $like, "%{$search}%");
                    });
            });
        }

        if ($request->filled('filter')) {
            $query->where('type', strtolower($request->filter));
        }

        $jobs = $query->latest()->paginate(10)->withQueryString();

        return view('dashboard', compact('jobs'));
    
    }
}
