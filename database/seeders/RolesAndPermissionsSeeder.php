<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('🔄 Creando permisos y roles...');

        // VERIFICACIÓN DE SEGURIDAD: contar usuarios existentes
        $existingUsersCount = User::count();
        if ($existingUsersCount > 0) {
            $this->command->warn("⚠️  ATENCIÓN: Se encontraron {$existingUsersCount} usuarios existentes");
            $this->command->info('✅ Este seeder NO eliminará usuarios existentes');
            
            // Opcional: preguntar confirmación en caso de duda
            // if (!$this->command->confirm('¿Continuar de forma segura?')) {
            //     $this->command->error('❌ Seeder cancelado por el usuario');
            //     return;
            // }
        }

        // --------------------------------
        // PERMISOS (usar firstOrCreate para no duplicar)
        // --------------------------------
        $permissions = [
            // Permisos globales (super admin)
            'manage all', // acceso total al sistema
            
            // Empresa (hierarchy_level = 0)
            'manage company', // configuración de empresa
            'manage company admins', // crear/editar admins
            'manage company users', // crear/editar usuarios
            'view company reports', // reportes de toda la empresa
            'export company data', // exportar datos empresariales
            
            // Administrador (hierarchy_level = 1)
            'manage own users', // solo usuarios bajo su control
            'view own reports', // reportes de su área
            'export own data', // exportar datos de su área
            
            // Usuario (hierarchy_level = 2)
            'use app', // usar la aplicación básica
            'view basic reports', // reportes básicos/personales
            
            // Operaciones específicas (para todos según rol)
            'create sales', // crear ventas
            'edit sales', // editar ventas
            'delete sales', // eliminar ventas
            'manage products', // gestionar productos
            'manage clients', // gestionar clientes
        ];

        $newPermissions = 0;
        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(['name' => $perm]);
            if ($permission->wasRecentlyCreated) {
                $newPermissions++;
            }
        }

        $this->command->info("📋 Permisos nuevos creados: {$newPermissions} | Total: " . count($permissions));

        // --------------------------------
        // ROLES (usar firstOrCreate para no duplicar)
        // --------------------------------
        $master = Role::firstOrCreate(['name' => 'master']); // Super admin del sistema
        $company = Role::firstOrCreate(['name' => 'company']); // Empresa (hierarchy_level = 0)
        $admin = Role::firstOrCreate(['name' => 'admin']); // Administrador (hierarchy_level = 1)
        $user = Role::firstOrCreate(['name' => 'user']); // Usuario (hierarchy_level = 2)

        // Mostrar qué roles se crearon
        $roleStatus = [
            'master' => $master->wasRecentlyCreated ? '🆕 Creado' : '✅ Ya existía',
            'company' => $company->wasRecentlyCreated ? '🆕 Creado' : '✅ Ya existía',
            'admin' => $admin->wasRecentlyCreated ? '🆕 Creado' : '✅ Ya existía',
            'user' => $user->wasRecentlyCreated ? '🆕 Creado' : '✅ Ya existía',
        ];

        foreach ($roleStatus as $roleName => $status) {
            $this->command->info("👥 Rol '{$roleName}': {$status}");
        }

        // --------------------------------
        // ASIGNAR PERMISOS A CADA ROL (syncPermissions es más seguro)
        // --------------------------------
        
        // MASTER: acceso total (para desarrollo/soporte)
        $master->syncPermissions(Permission::all());

        // COMPANY: gestión completa de su organización
        $company->syncPermissions([
            'manage company',
            'manage company admins',
            'manage company users',
            'view company reports',
            'export company data',
            'create sales',
            'edit sales',
            'delete sales',
            'manage products',
            'manage clients',
        ]);

        // ADMIN: gestión de usuarios bajo su control
        $admin->syncPermissions([
            'manage own users',
            'view own reports',
            'export own data',
            'create sales',
            'edit sales',
            'manage products',
            'manage clients',
        ]);

        // USER: operaciones básicas
        $user->syncPermissions([
            'use app',
            'view basic reports',
            'create sales',
            'edit sales',
            'manage clients', // pueden gestionar sus clientes
        ]);

        $this->command->info('🔗 Permisos sincronizados con roles');

        // --------------------------------
        // ASIGNAR MASTER DE FORMA SEGURA
        // --------------------------------
        $this->assignMasterRoleSafely();

        $this->command->info('✅ Roles y permisos configurados exitosamente');
    }

    /**
     * Asignar rol master de forma segura
     */
    private function assignMasterRoleSafely(): void
    {
        // Verificar si ya existe un usuario con rol master
        $existingMaster = User::role('master')->first();
        
        if ($existingMaster) {
            $this->command->info("🔑 Usuario master ya existe: {$existingMaster->email}");
            
            // Opcional: actualizar campos jerárquicos si no los tiene
            if ($existingMaster->hierarchy_level !== -1) {
                $existingMaster->update([
                    'hierarchy_level' => -1,
                    'parent_id' => null,
                    'is_active' => true,
                ]);
                $this->command->info("🔧 Campos jerárquicos actualizados para: {$existingMaster->email}");
            }
            return;
        }

        // Si no hay master, buscar el primer usuario para asignárselo
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('master');
            
            // Asegurar campos jerárquicos correctos
            $firstUser->update([
                'hierarchy_level' => -1, // Master level
                'parent_id' => null,
                'is_active' => true,
            ]);
            
            $this->command->info("🔑 Rol 'master' asignado al usuario: {$firstUser->email}");
        } else {
            $this->command->warn('⚠️  No hay usuarios en la base de datos para asignar rol master');
            $this->command->info('💡 Puedes crear un usuario master manualmente:');
            $this->command->line('   php artisan tinker');
            $this->command->line('   >>> User::create([');
            $this->command->line('       "name" => "Master Admin",');
            $this->command->line('       "email" => "admin@tudominio.com",');
            $this->command->line('       "password" => bcrypt("password"),');
            $this->command->line('       "hierarchy_level" => -1,');
            $this->command->line('       "is_active" => true');
            $this->command->line('   ])->assignRole("master");');
        }
    }
}