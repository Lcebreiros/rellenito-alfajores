<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneratedReport;
use App\Models\ReportConfiguration;
use App\Services\NexumReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class NexumReportController extends Controller
{
    // GET /api/nexum/reports
    public function index(Request $request)
    {
        $reports = GeneratedReport::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $reports->map(fn ($r) => $this->formatReport($r)),
        ]);
    }

    // POST /api/nexum/reports
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:weekly,monthly,quarterly,semiannual,annual',
        ]);

        $user   = $request->user();
        $period = $validated['period'];
        $end    = now()->endOfDay();
        $start  = match ($period) {
            'weekly'     => now()->subDays(7)->startOfDay(),
            'quarterly'  => now()->subMonths(3)->startOfDay(),
            'semiannual' => now()->subMonths(6)->startOfDay(),
            'annual'     => now()->subYear()->startOfDay(),
            default      => now()->subDays(30)->startOfDay(),
        };

        $service = new NexumReportService($user);
        $report  = $service->generate($start, $end, $period);

        return response()->json([
            'data' => $this->formatReport($report),
        ], 201);
    }

    // GET /api/nexum/reports/config
    public function getConfig(Request $request)
    {
        $config = $request->user()->reportConfiguration;

        return response()->json([
            'data' => $config ? $this->formatConfig($config) : null,
        ]);
    }

    // POST /api/nexum/reports/config
    public function saveConfig(Request $request)
    {
        $validated = $request->validate([
            'frequency'      => 'required|in:weekly,monthly,quarterly,semiannual,annual',
            'is_active'      => 'boolean',
            'email_delivery' => 'boolean',
        ]);

        $isActive = $validated['is_active'] ?? true;

        $config = ReportConfiguration::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'frequency'          => $validated['frequency'],
                'is_active'          => $isActive,
                'email_delivery'     => $validated['email_delivery'] ?? false,
                'next_generation_at' => $isActive
                    ? (new ReportConfiguration(['frequency' => $validated['frequency']]))->calculateNextGeneration()
                    : null,
            ]
        );

        return response()->json([
            'data' => $this->formatConfig($config),
        ]);
    }

    // DELETE /api/nexum/reports/{id}
    public function destroy(Request $request, int $id)
    {
        $report = GeneratedReport::forUser($request->user()->id)->findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Reporte eliminado exitosamente']);
    }

    // GET /api/nexum/reports/{id}/url
    public function downloadUrl(Request $request, int $id)
    {
        $report = GeneratedReport::forUser($request->user()->id)->findOrFail($id);

        if (! $report->isReady()) {
            return response()->json(['message' => 'El reporte no está listo aún'], 422);
        }

        $url = URL::temporarySignedRoute(
            'nexum.reports.signed-download',
            now()->addHour(),
            ['report' => $report->id]
        );

        return response()->json(['url' => $url]);
    }

    // ── Formatters ───────────────────────────────────────────────────────────

    private function formatReport(GeneratedReport $r): array
    {
        return [
            'id'                  => $r->id,
            'frequency_type'      => $r->frequency_type,
            'period_start'        => $r->period_start?->toDateString(),
            'period_end'          => $r->period_end?->toDateString(),
            'status'              => $r->status,
            'file_size'           => $r->file_size,
            'file_size_formatted' => $r->fileSizeFormatted(),
            'period_label'        => $r->periodLabel(),
            'created_at'          => $r->created_at?->toIso8601String(),
        ];
    }

    private function formatConfig(ReportConfiguration $c): array
    {
        return [
            'frequency'           => $c->frequency,
            'is_active'           => $c->is_active,
            'email_delivery'      => $c->email_delivery,
            'next_generation_at'  => $c->next_generation_at?->toIso8601String(),
        ];
    }
}
