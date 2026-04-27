<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Team;
use App\Models\LostfoundItem;
use App\Models\Announcement;
use App\Models\EventCategory;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Top Stats Cards
        $totalUsers = User::count();
        $usersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();
        
        $totalEvents = Event::count();
        $pendingEvents = Event::where('status', 'pending')->count();
        
        $totalTeams = Team::count();
        $openTeams = Team::where('status', 'approved')->get()->filter(function($team) {
            return $team->members()->where('status', 'active')->count() < $team->max_member;
        })->count();
        $pendingTeams = Team::where('status', 'pending')->count();
        
        $totalLF = LostfoundItem::count();
        $resolvedLF = LostfoundItem::where('status', 'resolved')->count();
        $unresolvedLF = LostfoundItem::whereNotIn('status', ['resolved'])->count();

        // 2. Growth Chart Data (Last 6 Months)
        $months = collect(range(5, 0))->map(function($i) {
            return now()->subMonths($i)->format('M');
        });

        $userGrowth = collect(range(5, 0))->map(function($i) {
            return User::whereMonth('created_at', now()->subMonths($i)->month)
                       ->whereYear('created_at', now()->subMonths($i)->year)
                       ->count();
        });

        $eventGrowth = collect(range(5, 0))->map(function($i) {
            return Event::whereMonth('created_at', now()->subMonths($i)->month)
                        ->whereYear('created_at', now()->subMonths($i)->year)
                        ->count();
        });

        $teamGrowth = collect(range(5, 0))->map(function($i) {
            return Team::whereMonth('created_at', now()->subMonths($i)->month)
                       ->whereYear('created_at', now()->subMonths($i)->year)
                       ->count();
        });

        // 3. Category Data
        $categories = EventCategory::withCount('events')->get();
        
        // 4. Status Distributions
        $teamStatus = [
            'open' => $openTeams,
            'full' => max(0, Team::where('status', 'approved')->count() - $openTeams)
        ];

        $lfStatus = [
            'lost' => LostfoundItem::where('status', 'lost')->count(),
            'found' => LostfoundItem::where('status', 'found')->count(),
            'resolved' => $resolvedLF
        ];

        // 5. Recent Activities (Merged and Sorted)
        $recentEvents = Event::with('creator')->latest('created_at')->take(5)->get()->map(function($e) {
            return [
                'type' => 'event',
                'title' => $e->title,
                'user' => $e->creator->name ?? 'User',
                'status' => $e->status,
                'created_at' => $e->created_at
            ];
        });

        $recentTeams = Team::latest('created_at')->take(5)->get()->map(function($t) {
            return [
                'type' => 'team',
                'title' => $t->team_name,
                'status' => $t->status,
                'created_at' => $t->created_at
            ];
        });

        $recentLF = LostfoundItem::latest('created_at')->take(5)->get()->map(function($l) {
            return [
                'type' => 'lostfound',
                'title' => $l->item_name,
                'status' => $l->status,
                'created_at' => $l->created_at
            ];
        });

        $activities = $recentEvents->concat($recentTeams)->concat($recentLF)
            ->sortByDesc('created_at')
            ->take(5);

        // 6. Latest Announcements
        $announcements = Announcement::latest()->take(2)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'usersThisWeek',
            'totalEvents', 'pendingEvents',
            'totalTeams', 'openTeams', 'pendingTeams',
            'totalLF', 'resolvedLF', 'unresolvedLF',
            'months', 'userGrowth', 'eventGrowth', 'teamGrowth',
            'categories', 'teamStatus', 'lfStatus',
            'activities', 'announcements'
        ));
    }
}
