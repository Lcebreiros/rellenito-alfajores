<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductLocation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillProductLocations extends Command
{
    protected $signature = 'stock:backfill-locations
        {--company= : Limitar a una empresa (user_id de company)}
        {--branch= : Limitar a una sucursal (user_id del admin de sucursal)}
        {--dry-run : No realiza escrituras}
        {--move : Mueve stock desde products.stock a product_locations (y deja products.stock en 0)}
        {--fix-branch-ids : Corrige product_locations.branch_id que apunten al ID del Branch en vez del user.id}';

    protected $description = 'Crea/normaliza product_locations para productos de sucursal y corrige branch_id legados.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $move = (bool) $this->option('move');
        $fix  = (bool) $this->option('fix-branch-ids');
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $branchUserId = $this->option('branch') ? (int) $this->option('branch') : null;

        if ($fix) {
            $this->fixLegacyBranchIds($dry);
        }

        $this->backfillMissingLocations($dry, $move, $companyId, $branchUserId);

        $this->info('Done.');
        return Command::SUCCESS;
    }

    private function fixLegacyBranchIds(bool $dry): void
    {
        $this->info('Corrigiendo branch_id legados en product_locations…');

        // Mapa Branch.id -> user.id (admin de sucursal)
        $map = Branch::query()->with('user:id,representable_id')
            ->get(['id'])
            ->filter(fn($b) => $b->user && (int)$b->user->representable_id === (int)$b->id)
            ->mapWithKeys(fn($b) => [(int)$b->id => (int)$b->user->id])
            ->toArray();

        if (empty($map)) {
            $this->line('No se encontró mapeo Branch->User.');
            return;
        }

        $affected = 0;
        foreach ($map as $branchId => $userId) {
            // Actualizar filas donde branch_id coincide con Branch.id
            $count = ProductLocation::query()->where('branch_id', $branchId)->count();
            if ($count === 0) continue;

            $this->line(" - Branch {$branchId} -> User {$userId} ({$count} filas)");
            if (!$dry) {
                DB::table('product_locations')
                    ->where('branch_id', $branchId)
                    ->update(['branch_id' => $userId]);
            }
            $affected += $count;
        }

        $this->info("Filas actualizadas: {$affected}");
    }

    private function backfillMissingLocations(bool $dry, bool $move, ?int $companyId, ?int $branchUserId): void
    {
        $this->info('Backfilling de ubicaciones faltantes para productos de sucursal…');

        $products = Product::query()
            ->select(['id','user_id','company_id','stock'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchUserId, fn($q) => $q->where('user_id', $branchUserId))
            ->where('stock', '>', 0)
            ->whereDoesntHave('productLocations')
            ->with('user:id,parent_id')
            ->orderBy('id')
            ->cursor();

        $created = 0; $moved = 0;

        foreach ($products as $p) {
            /** @var Product $p */
            $owner = $p->user; if (!$owner) continue;

            // Determinar sucursal asociada al producto: el propio user si es admin, o su parent si es empleado
            $branchUser = null;
            if (method_exists($owner, 'isAdmin') && $owner->isAdmin()) {
                $branchUser = $owner;
            } elseif (!empty($owner->parent_id)) {
                $parent = User::find($owner->parent_id);
                if ($parent && method_exists($parent, 'isAdmin') && $parent->isAdmin()) {
                    $branchUser = $parent;
                }
            }

            if (!$branchUser) continue;

            $this->line(" - Producto {$p->id}: crear location en branch user {$branchUser->id} con stock {$p->stock}");
            if (!$dry) {
                ProductLocation::updateStock((int)$p->id, (int)$branchUser->id, (float)$p->stock);
                $created++;
                if ($move) {
                    // Dejar products.stock en 0 si movemos
                    DB::table('products')->where('id', $p->id)->update(['stock' => 0]);
                    $moved++;
                }
            }
        }

        $this->info("Ubicaciones creadas: {$created}. Movidos a locations: {$moved}");
    }
}

