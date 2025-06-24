<?php
namespace App\Console\Commands;

use App\Models\PortalEbook;
use App\Models\PortalEmagazine;
use App\Models\PortalEsertifikat;
use App\Models\PortalGaleri;
use App\Models\PortalInfografis;
use App\Models\PortalPage;
use App\Models\PortalPost;
use App\Models\PortalUnduhan;
use App\Models\PortalVideo;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PublishScheduledContent extends Command
{
    protected $signature   = 'content:publish-scheduled';
    protected $description = 'Publish scheduled content if the scheduled date and time have arrived';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->publishScheduled(PortalPost::class);
        $this->publishScheduled(PortalPage::class);
        $this->publishScheduled(PortalInfografis::class);
        $this->publishScheduled(PortalEbook::class);
        $this->publishScheduled(PortalEmagazine::class);
        $this->publishScheduled(PortalEsertifikat::class);
        $this->publishScheduled(PortalVideo::class);
        $this->publishScheduled(PortalUnduhan::class);
        $this->publishScheduled(PortalGaleri::class);

        $this->info('Scheduled content has been checked and published if due.');
    }

    private function publishScheduled($model)
    {
        $now = Carbon::now();

        $model::where('status', 'Scheduled')
            ->where('tanggal', '<=', $now)
            ->update(['status' => 'Published']);
    }
}
