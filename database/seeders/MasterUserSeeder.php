<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MasterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que el rol 'master' exista
        Role::firstOrCreate(['name' => 'master', 'guard_name' => 'web']);

        // Crear o buscar al usuario
        $master = User::firstOrCreate(
            ['email' => 'leancebreiros@hotmail.com'],
            [
                'name' => 'Gestior',
                'password' => Hash::make('leandro1'), // mejor que bcrypt()
                'parent_id' => null,
                'hierarchy_level' => User::HIERARCHY_MASTER,
                'is_active' => true,
                'user_limit' => null, // sin límite
            ]
        );

        // Asignar rol si aún no lo tiene
        if (!$master->hasRole('master')) {
            $master->assignRole('master');
        }
    }
}
