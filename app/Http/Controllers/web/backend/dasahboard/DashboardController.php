<?php
namespace App\Http\Controllers\web\backend\dasahboard;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalActor;
use App\Models\PortalBanner;
use App\Models\PortalEbook;
use App\Models\PortalEmagazine;
use App\Models\PortalEsertifikat;
use App\Models\PortalFAQ;
use App\Models\PortalGaleri;
use App\Models\PortalGreeting;
use App\Models\PortalInfografis;
use App\Models\PortalPage;
use App\Models\PortalPost;
use App\Models\PortalRunningText;
use App\Models\PortalUnduhan;
use App\Models\PortalVideo;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Definisikan status sebagai konstanta
    public const STATUS_PUBLISHED = 'Published';
    public const STATUS_ARCHIVED  = 'Archived';
    public const STATUS_ACTIVE    = '1';
    public const STATUS_INACTIVE  = '0';

    // Daftar model dan URL routing
    protected $models = [
        'Postingan'   => PortalPost::class,
        'Halaman'     => PortalPage::class,
        'Banner'      => PortalBanner::class,
        'Infografis'  => PortalInfografis::class,
        'Galeri'      => PortalGaleri::class,
        'Video'       => PortalVideo::class,
        'Ebook'       => PortalEbook::class,
        'Emagazine'   => PortalEmagazine::class,
        'Esertifikat' => PortalEsertifikat::class,
        'Unduhan'     => PortalUnduhan::class,
        // 'Testimoni'   => PortalTestimoni::class,
        'RunningText' => PortalRunningText::class,
        'Greeting'    => PortalGreeting::class,
        'FAQ'         => PortalFAQ::class,
    ];

    protected $urls = [
        'Postingan'   => 'prt.apps.post.index',
        'Halaman'     => 'prt.apps.page.index',
        'Banner'      => 'prt.apps.banner.index',
        'Infografis'  => 'prt.apps.infografis.index',
        'Galeri'      => 'prt.apps.galeri.index',
        'Video'       => 'prt.apps.video.index',
        'Ebook'       => 'prt.apps.ebook.index',
        'Emagazine'   => 'prt.apps.emagazine.index',
        'Esertifikat' => 'prt.apps.esertifikat.index',
        'Unduhan'     => 'prt.apps.unduhan.index',
        // 'Testimoni'   => 'prt.apps.testimoni.index',
        'RunningText' => 'prt.apps.runningtext.index',
        'Greeting'    => 'prt.apps.greeting.index',
        'FAQ'         => 'prt.apps.faq.index',
    ];

    // Metode untuk mendapatkan status berdasarkan model tertentu
    protected function getStatusCounts($model, $category)
    {
        $auth = Auth::user();
        $role = $auth->role;

        $uuid_user       = $auth->uuid;
        $status_active   = in_array($category, ['Banner', 'RunningText', 'Greeting', 'FAQ']) ? self::STATUS_ACTIVE : self::STATUS_PUBLISHED;
        $status_inactive = in_array($category, ['Banner', 'RunningText', 'Greeting', 'FAQ']) ? self::STATUS_INACTIVE : self::STATUS_ARCHIVED;

        if ($role === "Super Admin" || $role === "Admin" || $role === "Editor") {
            return [
                'published' => $model::whereStatus($status_active)->count(),
                'archived'  => $model::whereStatus($status_inactive)->count(),
            ];
        } else {
            return [
                'published' => $model::whereUuidCreated($uuid_user)->whereStatus($status_active)->count(),
                'archived'  => $model::whereUuidCreated($uuid_user)->whereStatus($status_inactive)->count(),
            ];
        }
    }

    //index
    public function index(Request $request)
    {
        $auth = Auth::user();
        $role = $auth->role;

        // Jika peran adalah "Kontributor" atau "Penulis", filter kategori yang diperbolehkan
        $allowedCategories = $role == "Kontributor" || $role == "Penulis"
        ? ['Postingan', 'Infografis', 'Galeri', 'Video', 'Ebook', 'Emagazine', 'Esertifikat', 'Unduhan']
        : array_keys($this->models);

        $data         = [];
        $sumActors    = 0;
        $sumPublished = 0;
        $sumArchived  = 0;

        foreach ($this->models as $category => $model) {
            if (! in_array($category, $allowedCategories)) {
                continue;
            }
            $statusCounts = $this->getStatusCounts($model, $category);
            $sumPublished += $statusCounts['published'];
            $sumArchived += $statusCounts['archived'];

            if ($category === "Halaman") {
                $url = route('prt.apps.page.index', [\Helper::encode('Profile')]);
            } else {
                $url = route($this->urls[$category]);
            }

            $data[] = [
                'kategori'  => $category,
                'url'       => $url,
                'published' => Helper::toDot($statusCounts['published']),
                'archived'  => Helper::toDot($statusCounts['archived']),
            ];
        }

        $sumActors = PortalActor::count();
        $sumKonten = $sumPublished + $sumArchived;
        $statistik = [
            "users"     => Helper::toDot($sumActors),
            "konten"    => Helper::toDot($sumKonten),
            "published" => Helper::toDot($sumPublished),
            "archived"  => Helper::toDot($sumArchived),
        ];

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('kategori', function ($data) {
                    return '<a href="' . $data['url'] . '">' . $data['kategori'] . '</a>';
                })
                ->addColumn('aksi', function ($data) {
                    return '
                        <div class="d-flex">
                        <a href="' . $data['url'] . '" class="btn btn-primary shadow btn-xs me-1"><i class="fa-solid fa-folder-tree me-2"></i>Buka Data</a>
                        ';
                })
                ->setRowId('uuid')
                ->escapeColumns([])
                ->make(true);
        }

        $view = $role == "Super Admin" || $role == "Admin" || $role == "Editor"
        ? 'pages.admin.portal_apps.dashboard.admin'
        : ($role == "Kontributor" || $role == "Penulis" ? 'pages.admin.portal_apps.dashboard.penulis' : 'pages.admin.portal_apps.dashboard.operator');

        return view($view, compact('data', 'statistik'));
    }
}
