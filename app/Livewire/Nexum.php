<?php

namespace App\Livewire;

use App\Jobs\GenerateBusinessInsights;
use App\Models\BusinessInsight;
use App\Models\GeneratedReport;
use App\Models\ReportConfiguration;
use App\Models\User;
use App\Services\HealthReportService;
use App\Services\Insights\InsightService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    // ─── Multi-tenant: siempre operar sobre la empresa raíz ──────────────

    private function companyUser(): User
    {
        $user = Auth::user();
        return $user->isCompany() ? $user : ($user->rootCompany() ?? $user);
    }

    private function companyUserId(): int
    {
        return $this->companyUser()->id;
    }

    // ─── Mount ───────────────────────────────────────────────────────────

    public function mount(): void
    {
        $config = Auth::user()->reportConfiguration;
        if ($config) {
            $this->frequency     = $config->frequency;
            $this->isActive      = $config->is_active;
            $this->emailDelivery = $config->email_delivery;
        }

        // Si hay una generación en curso (page refresh mid-job), restaurar estado
        if (Cache::has("nexum_pending_{$this->companyUserId()}")) {
            $this->generating = true;
        }
    }

    // ─── Insights ────────────────────────────────────────────────────────

    public function getInsightsProperty()
    {
        $query = BusinessInsight::forUser($this->companyUserId())
            ->active()
            ->orderByPriority();

        if ($this->filter === 'critical') {
            $query->ofPriority('critical');
        } elseif ($this->filter !== 'all') {
            $query->ofType($this->filter);
        }

        return $query->get();
    }

    public function getHasAiInsightsProperty(): bool
    {
        return $this->companyUser()->hasAiInsights();
    }

    public function getHealthReportProperty(): array
    {
        try {
            return (new HealthReportService($this->companyUser()))->generate();
        } catch (\Throwable) {
            return ['overall_score' => 0, 'status' => 'Sin datos', 'status_color' => '#6b7280', 'categories' => []];
        }
    }

    public function getReportsProperty()
    {
        return GeneratedReport::forUser($this->companyUserId())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function getStatsProperty(): array
    {
        return (new InsightService())->getStats($this->companyUser());
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    // ─── Generación asíncrona ─────────────────────────────────────────────

    public function generate(): void
    {
        $userId = $this->companyUserId();

        // Rate limiting: máximo 1 generación cada 90 segundos
        $lastKey = "nexum_last_gen_{$userId}";
        $lastGen = Cache::get($lastKey);
        if ($lastGen) {
            $elapsed   = now()->diffInSeconds($lastGen);
            $cooldown  = 90;
            if ($elapsed < $cooldown) {
                $remaining = $cooldown - $elapsed;
                session()->flash('nexum_error', "Esperá {$remaining}s antes de actualizar nuevamente.");
                return;
            }
        }

        // Evitar doble dispatch si ya hay uno en cola
        if (Cache::has("nexum_pending_{$userId}")) {
            session()->flash('nexum_error', 'Ya hay un análisis en curso, esperá que termine.');
            return;
        }

        // Marcar como en progreso
        $this->generating = true;
        Cache::put("nexum_pending_{$userId}", true, 120);
        Cache::put($lastKey, now(), 3600);
        Cache::forget("nexum_result_{$userId}");

        GenerateBusinessInsights::dispatch($this->companyUser(), null, true);
    }

    /**
     * Polled desde el blade cada 2.5s mientras $generating = true.
     * Lee el resultado que el Job deposita en cache y actualiza el estado.
     */
    public function checkGeneration(): void
    {
        if (!$this->generating) {
            return;
        }

        $userId = $this->companyUserId();

        $result = Cache::pull("nexum_result_{$userId}");
        if ($result !== null) {
            $this->generating = false;
            if ($result['success']) {
                session()->flash('nexum_success', $result['message']);
            } else {
                session()->flash('nexum_error', $result['message']);
            }
            return;
        }

        // Si la clave pending expiró sin resultado, el job falló silenciosamente
        if (!Cache::has("nexum_pending_{$userId}")) {
            $this->generating = false;
            session()->flash('nexum_error', 'El análisis no pudo completarse. Intentá nuevamente.');
        }
    }

    public function dismiss(int $id): void
    {
        (new InsightService())->dismissInsight($id, $this->companyUser());
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

    public function requestManualReport(string $period = 'monthly'): void
    {
        $this->requestingManual = true;

        $user = Auth::user();
        $end  = now()->endOfDay();
        $start = match($period) {
            'weekly'     => now()->subDays(7)->startOfDay(),
            'quarterly'  => now()->subMonths(3)->startOfDay(),
            'semiannual' => now()->subMonths(6)->startOfDay(),
            'annual'     => now()->subYear()->startOfDay(),
            default      => now()->subDays(30)->startOfDay(),
        };

        try {
            $service = new \App\Services\NexumReportService($user);
            $report  = $service->generate($start, $end, $period);

            // Notificación en panel
            \App\Models\UserNotification::create([
                'user_id' => $user->id,
                'type'    => 'report_ready',
                'title'   => '📊 Reporte listo',
                'message' => 'Tu reporte manual de Nexum está listo para descargar.',
                'data'    => [
                    'report_id'    => $report->id,
                    'period_start' => $start->toDateString(),
                    'period_end'   => $end->toDateString(),
                    'frequency'    => 'manual',
                    'url'          => '/nexum',
                ],
                'is_read' => false,
            ]);

            // Email si lo activó
            if ($user->reportConfiguration?->email_delivery) {
                $user->notify(new \App\Notifications\ReportReadyNotification($report));
            }

            // Transicionar modal a estado "listo"
            $this->dispatch('report-ready',
                reportId:    $report->id,
                viewUrl:     route('nexum.reports.view', $report),
                downloadUrl: route('nexum.reports.download', $report),
            );
        } catch (\Throwable $e) {
            $this->dispatch('report-failed');
            session()->flash('nexum_error', 'Error al generar el reporte: ' . $e->getMessage());
        }

        $this->requestingManual = false;
    }

    public function downloadReport(int $id): mixed
    {
        $report = GeneratedReport::forUser($this->companyUserId())->findOrFail($id);

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

    public function deleteReport(int $id): void
    {
        $report = GeneratedReport::forUser($this->companyUserId())->findOrFail($id);

        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        $report->delete();

        session()->flash('nexum_success', 'Reporte eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.nexum');
    }
}
