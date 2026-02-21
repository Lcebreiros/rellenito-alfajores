<?php

namespace App\Console\Commands;

use App\Jobs\GenerateReportJob;
use App\Models\GeneratedReport;
use App\Models\ReportConfiguration;
use Illuminate\Console\Command;

class GenerateScheduledReports extends Command
{
    protected $signature   = 'reports:generate-scheduled {--user= : ID de usuario específico} {--dry-run : Ver qué reportes se generarían sin generarlos}';
    protected $description = 'Genera reportes programados para usuarios cuya fecha de generación haya llegado.';

    public function handle(): int
    {
        $query = ReportConfiguration::query()
            ->active()
            ->due()
            ->with('user');

        if ($this->option('user')) {
            $query->where('user_id', $this->option('user'));
        }

        $configs = $query->get();

        if ($configs->isEmpty()) {
            $this->info('No hay reportes programados pendientes.');
            return self::SUCCESS;
        }

        $this->info("Procesando {$configs->count()} configuración(es) de reporte...");

        foreach ($configs as $config) {
            $user = $config->user;
            if (!$user) continue;

            [$start, $end] = $config->getPeriodDates();

            if ($this->option('dry-run')) {
                $this->line("  [DRY-RUN] Usuario #{$user->id} ({$user->name}) — {$config->frequencyLabel()} — {$start->format('d/m/Y')} al {$end->format('d/m/Y')}");
                continue;
            }

            // Crear el registro del reporte
            $report = GeneratedReport::create([
                'user_id'        => $user->id,
                'frequency_type' => $config->frequency,
                'period_start'   => $start->toDateString(),
                'period_end'     => $end->toDateString(),
                'status'         => 'pending',
            ]);

            // Despachar job en background
            GenerateReportJob::dispatch(
                $user->id,
                $report->id,
                $start->toDateString(),
                $end->toDateString(),
                $config->frequency
            );

            // Actualizar config: marcar cuándo se generó y cuándo es el próximo
            $config->update([
                'last_generated_at'  => now(),
                'next_generation_at' => $config->calculateNextGeneration(),
            ]);

            $this->line("  ✓ Reporte #{$report->id} encolado para {$user->name} ({$config->frequencyLabel()})");
        }

        $this->info('Listo.');
        return self::SUCCESS;
    }
}
