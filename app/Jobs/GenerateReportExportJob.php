<?php

namespace App\Jobs;

use App\Actions\Notifications\SendTenantNotificationAction;
use App\Exports\MonthlyDuesExport;
use App\Exports\PaymentsExport;
use App\Exports\StudentsExport;
use App\Models\Tenant;
use App\Notifications\ReportExportReadyNotification;
use App\Support\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Runs a report export in the background instead of holding the request
 * open — the export classes' WithChunkReading keeps memory flat regardless
 * of row count, but a big report can still take long enough that a
 * synchronous HTTP response isn't acceptable (see the analysis doc's edge
 * case #15). Whoever requested it gets an email with a signed download link
 * once the file is ready — see ReportsIndex for the row-count threshold
 * that decides sync-download vs this job.
 */
class GenerateReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        protected int $tenantId,
        protected string $reportType,
        protected array $filters,
        protected string $filename,
        protected string $recipientEmail,
        protected string $reportLabel,
    ) {}

    public function handle(TenantContext $context, SendTenantNotificationAction $notifier): void
    {
        $tenant = Tenant::query()->findOrFail($this->tenantId);
        $context->set($tenant);

        $export = match ($this->reportType) {
            'students' => new StudentsExport($this->filters),
            'payments' => new PaymentsExport($this->filters),
            'monthly_dues' => new MonthlyDuesExport($this->filters),
            default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
        };

        $path = "tenants/{$tenant->id}/reports/{$this->filename}";
        $export->store($path, 'local');

        $context->set(null);

        $signedUrl = URL::temporarySignedRoute(
            'tenant.reports.download',
            now()->addHours(24),
            ['tenant' => $tenant->slug, 'filename' => $this->filename],
        );

        $notifier->execute(
            new ReportExportReadyNotification($this->reportLabel, $signedUrl),
            $tenant,
            $this->recipientEmail,
        );
    }
}
