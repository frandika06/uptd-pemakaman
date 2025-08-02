<?php
namespace App\Http\Controllers\web\backend\log;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalActor;
use App\Models\SysLogAktifitas;
use App\Models\SysLogin;
use App\Models\TpuPetugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AuditTrailController extends Controller
{
    /**
     * Display audit trail index
     */
    public function index(Request $request)
    {
        $auth = Auth::user();
        $role = $auth->role;

        // Initialize filter for log type
        $logType = $request->session()->get('filter_log_type', 'Semua Log');
        if ($request->ajax() && isset($_GET['filter']['type'])) {
            $logType = $_GET['filter']['type'];
            $request->session()->put('filter_log_type', $logType);
        }

        if ($request->ajax()) {
            // Get combined logs based on role and filter
            $combinedLogs = $this->getCombinedLogs($auth, $role, $logType);

            return DataTables::of($combinedLogs)
                ->addIndexColumn()
                ->setRowId('created_at')
                ->addColumn('type', function ($row) {
                    $color = $row['type'] == 'Login' ? 'primary' : 'info';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $row['type'] . '</span>';
                })
                ->addColumn('user_name', function ($row) use ($role) {
                    if (in_array($role, ['Super Admin', 'Admin']) && $row['user_name']) {
                        $foto = Helper::pp($row['user_foto'] ?? '');
                        return '
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px symbol-circle me-3">
                                    <img src="' . $foto . '" alt="' . htmlspecialchars($row['user_name']) . '" class="object-fit-cover" />
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">' . htmlspecialchars($row['user_name']) . '</span>
                                    <span class="text-muted fs-7">' . htmlspecialchars($row['user_role'] ?? 'Unknown') . '</span>
                                </div>
                            </div>
                        ';
                    }
                    return null;
                })
                ->addColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row['created_at'])->format('d/m/Y H:i');
                })
                ->addColumn('activity', function ($row) {
                    return $row['activity'] ?? '-';
                })
                ->addColumn('description', function ($row) {
                    return Str::limit($row['description'] ?? '-', 100);
                })
                ->escapeColumns([])
                ->make(true);
        }

        // Log type options for filter
        $logTypes = ['Semua Log', 'Login', 'Activity'];
        return view('admin.setup.log.index', compact('auth', 'role', 'logType', 'logTypes'));
    }

    /**
     * Get combined login and activity logs based on role and filter
     */
    private function getCombinedLogs($auth, $role, $logType)
    {
        $loginLogs    = $this->getLoginLogs($auth, $role, $logType);
        $activityLogs = $this->getActivityLogs($auth, $role, $logType);

        return $loginLogs->map(function ($log) use ($role) {
            return [
                'type'        => 'Login',
                'created_at'  => $log->created_at,
                'user_name'   => in_array($role, ['Super Admin', 'Admin']) ? ($log->user_name ?? $log->user_email ?? '-') : null,
                'user_foto'   => in_array($role, ['Super Admin', 'Admin']) ? ($log->user_foto ?? null) : null,
                'user_role'   => in_array($role, ['Super Admin', 'Admin']) ? ($log->user_role ?? null) : null,
                'activity'    => $log->status ?? '-',
                'description' => $log->status ?? '-',
            ];
        })->merge($activityLogs->map(function ($log) use ($role) {
            return [
                'type'        => 'Activity',
                'created_at'  => $log->created_at,
                'user_name'   => in_array($role, ['Super Admin', 'Admin']) ? ($log->user_name ?? '-') : null,
                'user_foto'   => in_array($role, ['Super Admin', 'Admin']) ? ($log->user_foto ?? null) : null,
                'user_role'   => in_array($role, ['Super Admin', 'Admin']) ? ($log->user_role ?? null) : null,
                'activity'    => $log->subjek ?? '-',
                'description' => json_decode($log->aktifitas, true)['description'] ?? $log->subjek ?? '-',
            ];
        }))->sortByDesc('created_at')->take(100);
    }

    /**
     * Get login logs based on role and filter
     */
    private function getLoginLogs($auth, $role, $logType)
    {
        if ($logType != 'Semua Log' && $logType != 'Login') {
            return collect([]);
        }

        if (in_array($role, ['Super Admin', 'Admin'])) {
            return $this->getAdminLoginLogs();
        }

        return $this->getUserLoginLogs($auth);
    }

    /**
     * Get activity logs based on role and filter
     */
    private function getActivityLogs($auth, $role, $logType)
    {
        if ($logType != 'Semua Log' && $logType != 'Activity') {
            return collect([]);
        }

        if (in_array($role, ['Super Admin', 'Admin'])) {
            return $this->getAdminActivityLogs();
        }

        return $this->getUserActivityLogs($auth);
    }

    /**
     * Get login logs for Admin/Super Admin (all users)
     */
    private function getAdminLoginLogs()
    {
        // Get login logs with user data from both portal_actor and tpu_petugas
        return SysLogin::join('users', 'sys_login.uuid_profile', '=', 'users.uuid')
            ->leftJoin('portal_actor', 'users.uuid', '=', 'portal_actor.uuid_user')
            ->leftJoin('tpu_petugas', 'users.uuid', '=', 'tpu_petugas.uuid_user')
            ->select(
                'sys_login.created_at',
                'sys_login.status',
                'users.username as user_email',
                'users.role as user_role',
                // Use COALESCE to get name from either table
                \DB::raw('COALESCE(portal_actor.nama_lengkap, tpu_petugas.nama_lengkap) as user_name'),
                \DB::raw('COALESCE(portal_actor.foto, tpu_petugas.foto) as user_foto')
            )
            ->orderBy('sys_login.created_at', 'desc')
            ->get();
    }

    /**
     * Get login logs for current user
     */
    private function getUserLoginLogs($auth)
    {
        return SysLogin::where('uuid_profile', $auth->uuid)
            ->select(
                'created_at',
                'status'
            )
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get activity logs for Admin/Super Admin (all users)
     */
    private function getAdminActivityLogs()
    {
        // Get activity logs from both portal_actor and tpu_petugas users
        $portalActivities = SysLogAktifitas::join('portal_actor', 'sys_log_aktifitas.uuid_profile', '=', 'portal_actor.uuid')
            ->join('users', 'portal_actor.uuid_user', '=', 'users.uuid')
            ->select(
                'sys_log_aktifitas.created_at',
                'sys_log_aktifitas.subjek',
                'sys_log_aktifitas.aktifitas',
                'portal_actor.nama_lengkap as user_name',
                'portal_actor.foto as user_foto',
                'users.role as user_role'
            )
            ->get();

        $tpuActivities = SysLogAktifitas::join('tpu_petugas', 'sys_log_aktifitas.uuid_profile', '=', 'tpu_petugas.uuid')
            ->join('users', 'tpu_petugas.uuid_user', '=', 'users.uuid')
            ->select(
                'sys_log_aktifitas.created_at',
                'sys_log_aktifitas.subjek',
                'sys_log_aktifitas.aktifitas',
                'tpu_petugas.nama_lengkap as user_name',
                'tpu_petugas.foto as user_foto',
                'users.role as user_role'
            )
            ->get();

        return $portalActivities->merge($tpuActivities)->sortByDesc('created_at');
    }

    /**
     * Get activity logs for current user
     */
    private function getUserActivityLogs($auth)
    {
        $role = $auth->role;

        // Check if user is TPU user
        if (in_array($role, ['Admin TPU', 'Petugas TPU'])) {
            $tpuPetugas = TpuPetugas::where('uuid_user', $auth->uuid)->first();
            if (! $tpuPetugas) {
                return collect([]);
            }

            return SysLogAktifitas::where('uuid_profile', $tpuPetugas->uuid)
                ->select(
                    'created_at',
                    'subjek',
                    'aktifitas'
                )
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // For portal users
        $portalActor = PortalActor::where('uuid_user', $auth->uuid)->first();
        if (! $portalActor) {
            return collect([]);
        }

        return SysLogAktifitas::where('uuid_profile', $portalActor->uuid)
            ->select(
                'created_at',
                'subjek',
                'aktifitas'
            )
            ->orderBy('created_at', 'desc')
            ->get();
    }
}