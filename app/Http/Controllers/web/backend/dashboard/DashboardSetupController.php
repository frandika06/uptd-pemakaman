<?php
namespace App\Http\Controllers\web\backend\dashboard;

use App\Http\Controllers\Controller;
use App\Models\PortalActor;
use App\Models\PortalBanner;
use App\Models\PortalPesan;
use App\Models\PortalPost;
use App\Models\SysLogAktifitas;
use App\Models\SysLogin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            $data     = $this->getAdminDashboardData($auth);
            $viewPath = 'admin.setup.home.admin';
        } else {
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

        // Users Online (menggunakan Cache dari middleware LastSeen)
        $onlineUsers = 0;
        $allUsers    = User::whereStatus('1')->get();
        foreach ($allUsers as $user) {
            if (Cache::has('user-is-online-' . $user->uuid)) {
                $onlineUsers++;
            }
        }

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
        $recentLogins = SysLogin::join('users', 'sys_login.uuid_profile', '=', 'users.uuid')
            ->leftJoin('portal_actor', 'users.uuid', '=', 'portal_actor.uuid_user')
            ->select(
                'sys_login.created_at',
                'sys_login.status',
                'sys_login.ip',
                'sys_login.agent',
                'users.username as user_email',
                'users.role as user_role',
                'portal_actor.nama_lengkap as user_name',
                'portal_actor.foto as user_foto'
            )
            ->orderBy('sys_login.created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent System Activity Log
        $recentActivities = SysLogAktifitas::join('portal_actor', 'sys_log_aktifitas.uuid_profile', '=', 'portal_actor.uuid')
            ->join('users', 'portal_actor.uuid_user', '=', 'users.uuid')
            ->select(
                'sys_log_aktifitas.created_at',
                'sys_log_aktifitas.subjek',
                'sys_log_aktifitas.aktifitas',
                'sys_log_aktifitas.ip',
                'sys_log_aktifitas.agent',
                'portal_actor.nama_lengkap as user_name',
                'portal_actor.foto as user_foto',
                'users.role as user_role'
            )
            ->orderBy('sys_log_aktifitas.created_at', 'desc')
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

        // Users active in last 24 hours
        $usersActive24h = User::whereStatus('1')
            ->where('last_seen', '>=', Carbon::now()->subHours(24))
            ->count();

        return [
            'users'             => [
                'total'      => $totalUsers,
                'active'     => $activeUsers,
                'inactive'   => $inactiveUsers,
                'online'     => $onlineUsers,
                'active_24h' => $usersActive24h,
                'by_role'    => $usersByRole,
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
        $portalActor = PortalActor::where('uuid_user', $auth->uuid)->first();

        if (! $portalActor) {
            return [
                'personal' => [
                    'login_history'      => collect([]),
                    'activities'         => collect([]),
                    'last_login'         => null,
                    'login_count_30days' => 0,
                    'activity_stats'     => collect([]),
                ],
                'content'  => [
                    'my_posts'           => 0,
                    'my_published_posts' => 0,
                    'my_draft_posts'     => 0,
                ],
            ];
        }

        // Personal Login History
        $myLoginHistory = SysLogin::where('uuid_profile', $auth->uuid)
            ->select(
                'created_at',
                'status',
                'ip',
                'agent'
            )
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Personal Activity Log
        $myActivities = SysLogAktifitas::where('uuid_profile', $portalActor->uuid)
            ->select(
                'created_at',
                'subjek',
                'aktifitas',
                'ip',
                'agent'
            )
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Personal Content Statistics
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
        $activityStats = SysLogAktifitas::where('uuid_profile', $portalActor->uuid)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return [
            'personal' => [
                'login_history'      => $myLoginHistory->map(function ($log) use ($portalActor) {
                    return [
                        'created_at' => $log->created_at,
                        'status'     => $log->status,
                        'ip'         => $log->ip,
                        'agent'      => $log->agent,
                        'user_name'  => $portalActor->nama_lengkap ?? '-',
                        'user_foto'  => $portalActor->foto ?? null,
                    ];
                }),
                'activities'         => $myActivities->map(function ($log) use ($portalActor) {
                    return [
                        'created_at' => $log->created_at,
                        'subjek'     => $log->subjek,
                        'aktifitas'  => $log->aktifitas,
                        'ip'         => $log->ip,
                        'agent'      => $log->agent,
                        'user_name'  => $portalActor->nama_lengkap ?? '-',
                        'user_foto'  => $portalActor->foto ?? null,
                    ];
                }),
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
