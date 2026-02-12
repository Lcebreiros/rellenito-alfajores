<?php

namespace App\Console\Commands;

use App\Jobs\GenerateBusinessInsights;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insights:generate
                            {--user= : ID del usuario específico}
                            {--all : Generar para todos los usuarios}
                            {--clear : Limpiar insights existentes antes de generar}
                            {--sync : Ejecutar de forma síncrona (no usar queue)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera insights de negocio para usuarios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $all = $this->option('all');
        $clear = $this->option('clear');
        $sync = $this->option('sync');

        if (!$userId && !$all) {
            $this->error('Debes especificar --user={id} o --all');
            return 1;
        }

        if ($userId && $all) {
            $this->error('No puedes usar --user y --all al mismo tiempo');
            return 1;
        }

        // Generar para un usuario específico
        if ($userId) {
            $user = User::find($userId);

            if (!$user) {
                $this->error("Usuario con ID {$userId} no encontrado");
                return 1;
            }

            $this->info("Generando insights para: {$user->name} ({$user->email})");

            if ($sync) {
                $insightService = app(\App\Services\Insights\InsightService::class);
                $insights = $insightService->generateInsights($user, null, $clear);
                $this->info("✓ Generados {$insights->count()} insights");
            } else {
                GenerateBusinessInsights::dispatch($user, null, $clear);
                $this->info('✓ Job encolado');
            }

            return 0;
        }

        // Generar para todos los usuarios
        if ($all) {
            $this->info('Generando insights para todos los usuarios...');

            $count = 0;
            $bar = $this->output->createProgressBar(User::count());

            User::chunk(100, function ($users) use (&$count, $bar, $clear, $sync) {
                foreach ($users as $user) {
                    if ($sync) {
                        $insightService = app(\App\Services\Insights\InsightService::class);
                        $insightService->generateInsights($user, null, $clear);
                    } else {
                        GenerateBusinessInsights::dispatch($user, null, $clear);
                    }

                    $count++;
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
            $this->info("✓ Procesados {$count} usuarios");

            return 0;
        }
    }
}
