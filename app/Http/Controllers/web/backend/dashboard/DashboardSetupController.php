<?php
namespace App\Http\Controllers\web\backend\dashboard;

use App\Http\Controllers\Controller;
use App\Models\PortalBanner;
use App\Models\PortalPesan;
use App\Models\PortalPost;
use App\Models\SysLogAktifitas;
use App\Models\SysLogin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardSetupController extends Controller
{
    /**
     * Display dashboard setup
     */
    public function index(Request $request)
    {
        $auth = Auth::user();
        $role = $auth->role;

        // Data untuk semua role
        $data = [];

        // Role check untuk menentukan view dan data yang ditampilkan
        if (in_array($role, ['Super Admin', 'Admin'])) {
            // Data untuk Super Admin & Admin
            $data     = $this->getAdminDashboardData($auth);
            $viewPath = 'admin.setup.home.admin';
        } elseif (in_array($role, ['Editor', 'Penulis', 'Kontributor', 'Operator'])) {
            // Data untuk Editor, Penulis, Kontributor, Operator
            $data     = $this->getUserDashboardData($auth);
            $viewPath = 'admin.setup.home.user';
        } else {
            // Default fallback
            $data     = $this->getUserDashboardData($auth);
            $viewPath = 'admin.setup.home.user';
        }

        return view($viewPath, compact('data', 'auth', 'role'));
    }

    /**
     * Get dashboard data for Super Admin & Admin
     */
    private function getAdminDashboardData($auth)
    {
        // Statistik Users
        $totalUsers    = User::count();
        $activeUsers   = User::whereStatus('1')->count();
        $inactiveUsers = User::whereStatus('0')->count();
        $onlineUsers   = User::whereStatus('1')
            ->whereHas('RelPortalActor', function ($query) {
                $query->whereDate('updated_at', Carbon::today());
            })
            ->count();

        // Statistik berdasarkan Role
        $usersByRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get()
            ->keyBy('role')
            ->map(function ($item) {
                return $item->total;
            });

        // Login Activity (30 hari terakhir)
        $loginStats = SysLogin::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Recent Login Activity
        $recentLogins = SysLogin::with('RelPortalActor.RelUser')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // System Activity Log
        $recentActivities = SysLogAktifitas::with('RelPortalActor.RelUser')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Content Statistics
        $totalPosts     = PortalPost::count();
        $publishedPosts = PortalPost::whereStatus('Published')->count();
        $draftPosts     = PortalPost::whereStatus('Draft')->count();
        $totalBanners   = PortalBanner::count();
        $totalMessages  = PortalPesan::count();
        $unreadMessages = PortalPesan::where('status', 'Pending')->count();

        // Failed Login Attempts
        $failedLogins = DB::table('sys_failed_login')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return [
            'users'             => [
                'total'    => $totalUsers,
                'active'   => $activeUsers,
                'inactive' => $inactiveUsers,
                'online'   => $onlineUsers,
                'by_role'  => $usersByRole,
            ],
            'login_stats'       => $loginStats,
            'recent_logins'     => $recentLogins,
            'recent_activities' => $recentActivities,
            'content'           => [
                'total_posts'     => $totalPosts,
                'published_posts' => $publishedPosts,
                'draft_posts'     => $draftPosts,
                'total_banners'   => $totalBanners,
                'total_messages'  => $totalMessages,
                'unread_messages' => $unreadMessages,
            ],
            'security'          => [
                'failed_logins' => $failedLogins,
            ],
        ];
    }

    /**
     * Get dashboard data for Editor, Penulis, Kontributor, Operator
     */
    private function getUserDashboardData($auth)
    {
        // Personal Login History
        $myLoginHistory = SysLogin::where('uuid_profile', $auth->uuid)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Personal Activity Log
        $myActivities = SysLogAktifitas::where('uuid_user', $auth->uuid)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Personal Content Statistics (jika user adalah content creator)
        $myPosts          = PortalPost::where('uuid_created', $auth->uuid)->count();
        $myPublishedPosts = PortalPost::where('uuid_created', $auth->uuid)
            ->where('status', 'Published')
            ->count();
        $myDraftPosts = PortalPost::where('uuid_created', $auth->uuid)
            ->where('status', 'Draft')
            ->count();

        // Login Statistics (personal)
        $loginCount30Days = SysLogin::where('uuid_profile', $auth->uuid)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $lastLogin = SysLogin::where('uuid_profile', $auth->uuid)
            ->orderBy('created_at', 'desc')
            ->skip(1) // Skip current session
            ->first();

        // Activity count by days
        $activityStats = SysLogAktifitas::where('uuid_user', $auth->uuid)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return [
            'personal' => [
                'login_history'      => $myLoginHistory,
                'activities'         => $myActivities,
                'last_login'         => $lastLogin,
                'login_count_30days' => $loginCount30Days,
                'activity_stats'     => $activityStats,
            ],
            'content'  => [
                'my_posts'           => $myPosts,
                'my_published_posts' => $myPublishedPosts,
                'my_draft_posts'     => $myDraftPosts,
            ],
        ];
    }
}