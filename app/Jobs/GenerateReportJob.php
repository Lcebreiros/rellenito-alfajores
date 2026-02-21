<?php

namespace App\Jobs;

use App\Models\GeneratedReport;
use App\Models\User;
use App\Notifications\ReportReadyNotification;
use App\Services\NexumReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private int    $userId,
        private int    $reportId,
        private string $periodStart,
        private string $periodEnd,
        private string $frequencyType
    ) {}

    public function handle(): void
    {
        $user   = User::findOrFail($this->userId);
        $report = GeneratedReport::findOrFail($this->reportId);

        try {
            $service = new NexumReportService($user);
            $report  = $service->generate(
                Carbon::parse($this->periodStart),
                Carbon::parse($this->periodEnd),
                $this->frequencyType,
                $report
            );

            // Notificar al usuario
            $user->notify(new ReportReadyNotification($report));

            // También guardar en UserNotification para el panel
            \App\Models\UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'report_ready',
                'title'   => '📊 Reporte listo',
                'message' => 'Tu reporte ' . strtolower($report->periodLabel()) . ' de Nexum está listo para descargar.',
                'data'    => [
                    'report_id'    => $report->id,
                    'period_start' => $this->periodStart,
                    'period_end'   => $this->periodEnd,
                    'frequency'    => $this->frequencyType,
                    'url'          => '/nexum',
                ],
                'is_read' => false,
            ]);

        } catch (\Throwable $e) {
            Log::error('GenerateReportJob failed: ' . $e->getMessage(), [
                'user_id'   => $this->userId,
                'report_id' => $this->reportId,
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateReportJob permanently failed', [
            'user_id'   => $this->userId,
            'report_id' => $this->reportId,
            'error'     => $e->getMessage(),
        ]);

        GeneratedReport::where('id', $this->reportId)->update([
            'status'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);
    }
}
