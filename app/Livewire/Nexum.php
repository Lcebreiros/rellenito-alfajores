<?php

namespace App\Livewire;

use App\Jobs\GenerateReportJob;
use App\Models\BusinessInsight;
use App\Models\GeneratedReport;
use App\Models\ReportConfiguration;
use App\Services\HealthReportService;
use App\Services\Insights\InsightService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Nexum extends Component
{
    public string $filter      = 'all';
    public bool   $generating  = false;
    public bool   $showConfig  = false;

    // Config de reporte
    public string $frequency     = 'monthly';
    public bool   $isActive      = true;
    public bool   $emailDelivery = false;

    // Reporte manual
    public bool   $requestingManual = false;

    public function mount(): void
    {
        $config = Auth::user()->reportConfiguration;
        if ($config) {
            $this->frequency     = $config->frequency;
            $this->isActive      = $config->is_active;
            $this->emailDelivery = $config->email_delivery;
        }
    }

    // ─── Insights ────────────────────────────────────────────────────────

    public function getInsightsProperty()
    {
        $query = BusinessInsight::forUser(Auth::id())
            ->active()
            ->orderByPriority();

        if ($this->filter === 'critical') {
            $query->ofPriority('critical');
        } elseif ($this->filter !== 'all') {
            $query->ofType($this->filter);
        }

        return $query->get();
    }

    public function getHealthReportProperty(): array
    {
        try {
            return (new HealthReportService(Auth::user()))->generate();
        } catch (\Throwable) {
            return ['overall_score' => 0, 'status' => 'Sin datos', 'status_color' => '#6b7280', 'categories' => []];
        }
    }

    public function getReportsProperty()
    {
        return GeneratedReport::forUser(Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function getStatsProperty(): array
    {
        return (new InsightService())->getStats(Auth::user());
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function generate(): void
    {
        $this->generating = true;

        try {
            $user    = Auth::user();
            $service = new InsightService();
            $service->generateInsights($user, null, true);
            session()->flash('nexum_success', 'Insights generados correctamente.');
        } catch (\Throwable $e) {
            session()->flash('nexum_error', 'Error al generar insights: ' . $e->getMessage());
        }

        $this->generating = false;
    }

    public function dismiss(int $id): void
    {
        (new InsightService())->dismissInsight($id, Auth::user());
    }

    // ─── Config de reportes ──────────────────────────────────────────────

    public function saveConfig(): void
    {
        $this->validate([
            'frequency'     => 'required|in:weekly,monthly,quarterly,semiannual,annual',
            'isActive'      => 'boolean',
            'emailDelivery' => 'boolean',
        ]);

        $config = ReportConfiguration::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'frequency'          => $this->frequency,
                'is_active'          => $this->isActive,
                'email_delivery'     => $this->emailDelivery,
                'next_generation_at' => $this->isActive
                    ? (new ReportConfiguration(['frequency' => $this->frequency]))->calculateNextGeneration()
                    : null,
            ]
        );

        $this->showConfig = false;
        session()->flash('nexum_success', 'Configuración guardada. Próximo reporte: ' . optional($config->next_generation_at)->format('d/m/Y') . '.');
    }

    public function requestManualReport(): void
    {
        $this->requestingManual = true;

        $user  = Auth::user();
        $start = now()->subMonth()->startOfMonth();
        $end   = now()->subMonth()->endOfMonth();

        $report = GeneratedReport::create([
            'user_id'        => $user->id,
            'frequency_type' => 'manual',
            'period_start'   => $start->toDateString(),
            'period_end'     => $end->toDateString(),
            'status'         => 'pending',
        ]);

        GenerateReportJob::dispatch(
            $user->id,
            $report->id,
            $start->toDateString(),
            $end->toDateString(),
            'manual'
        );

        $this->requestingManual = false;
        session()->flash('nexum_success', 'Reporte solicitado. Recibirás una notificación cuando esté listo.');
    }

    public function downloadReport(int $id): mixed
    {
        $report = GeneratedReport::forUser(Auth::id())->findOrFail($id);

        if (!$report->isReady()) {
            session()->flash('nexum_error', 'El reporte no está disponible todavía.');
            return null;
        }

        $report->markDownloaded();

        return response()->streamDownload(function () use ($report) {
            echo Storage::get($report->file_path);
        }, 'nexum-reporte-' . $report->period_start->format('Y-m') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        return view('livewire.nexum');
    }
}
