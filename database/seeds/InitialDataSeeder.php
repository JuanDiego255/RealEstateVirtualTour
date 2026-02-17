<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Crear sectores por defecto
        $sectors = [
            [
                'name' => 'Bienes Raíces',
                'slug' => 'bienes-raices',
                'description' => 'Casas, apartamentos, lotes y terrenos',
                'icon' => 'fa-home',
                'color' => '#2563eb',
                'sort_order' => 1,
            ],
            [
                'name' => 'Vehículos',
                'slug' => 'vehiculos',
                'description' => 'Autos, motos, camiones y maquinaria',
                'icon' => 'fa-car',
                'color' => '#dc2626',
                'sort_order' => 2,
            ],
            [
                'name' => 'Comercial',
                'slug' => 'comercial',
                'description' => 'Locales comerciales, oficinas y bodegas',
                'icon' => 'fa-building',
                'color' => '#059669',
                'sort_order' => 3,
            ],
            [
                'name' => 'Alquileres',
                'slug' => 'alquileres',
                'description' => 'Propiedades y vehículos en alquiler',
                'icon' => 'fa-key',
                'color' => '#7c3aed',
                'sort_order' => 4,
            ],
        ];

        foreach ($sectors as $sector) {
            DB::table('sectors')->updateOrInsert(
                ['slug' => $sector['slug']],
                array_merge($sector, [
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }

        // 2. Crear paquetes por defecto
        $packages = [
            [
                'name' => 'Básico',
                'slug' => 'basico',
                'description' => 'Ideal para empezar. Incluye listado de propiedades sin tour virtual.',
                'price' => 15000.00,
                'currency' => 'CRC',
                'billing_period' => 'monthly',
                'max_users' => 2,
                'max_categories' => 1,
                'max_posts_per_category' => 20,
                'tour_price' => null,
                'allows_tours' => false,
                'allows_commission' => false,
                'trial_days' => 7,
                'features' => json_encode([
                    'support' => 'email',
                    'analytics' => false,
                ]),
                'sort_order' => 1,
            ],
            [
                'name' => 'Profesional',
                'slug' => 'profesional',
                'description' => 'Para agencias en crecimiento. Incluye tours virtuales y acceso a bolsa inmobiliaria.',
                'price' => 35000.00,
                'currency' => 'CRC',
                'billing_period' => 'monthly',
                'max_users' => 5,
                'max_categories' => 3,
                'max_posts_per_category' => 50,
                'tour_price' => 25000.00,
                'allows_tours' => true,
                'allows_commission' => true,
                'trial_days' => 14,
                'features' => json_encode([
                    'support' => 'priority',
                    'analytics' => true,
                ]),
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Para grandes agencias. Sin límites y con tours virtuales incluidos.',
                'price' => 75000.00,
                'currency' => 'CRC',
                'billing_period' => 'monthly',
                'max_users' => 999,
                'max_categories' => 10,
                'max_posts_per_category' => 200,
                'tour_price' => 0.00, // Incluido
                'allows_tours' => true,
                'allows_commission' => true,
                'trial_days' => 14,
                'features' => json_encode([
                    'support' => 'dedicated',
                    'analytics' => true,
                    'api_access' => true,
                ]),
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            DB::table('packages')->updateOrInsert(
                ['slug' => $package['slug']],
                array_merge($package, [
                    'is_active' => true,
                    'is_featured' => $package['is_featured'] ?? false,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }

        // 3. Crear empresa y categoría default para datos existentes
        $defaultCompanyId = DB::table('companies')->insertGetId([
            'name' => 'Empresa Default',
            'legal_name' => 'Sistema Virtual Tour',
            'description' => 'Empresa creada automáticamente para migrar datos existentes',
            'status' => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Obtener el sector de Bienes Raíces
        $sectorId = DB::table('sectors')->where('slug', 'bienes-raices')->value('id');

        $defaultCategoryId = DB::table('categories')->insertGetId([
            'company_id' => $defaultCompanyId,
            'sector_id' => $sectorId,
            'name' => 'Propiedades Migradas',
            'slug' => 'propiedades-migradas',
            'description' => 'Categoría default para propiedades existentes',
            'share_token' => Str::random(32),
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 4. Actualizar propiedades existentes para que pertenezcan a la categoría default
        DB::table('properties')
            ->whereNull('category_id')
            ->update([
                'category_id' => $defaultCategoryId,
                'published_at' => Carbon::now(),
            ]);

        // 5. Hacer al primer usuario super_admin si existe
        $firstUser = DB::table('users')->orderBy('id')->first();
        if ($firstUser) {
            DB::table('users')
                ->where('id', $firstUser->id)
                ->update([
                    'role' => 'super_admin',
                    'company_id' => $defaultCompanyId,
                ]);

            // Asignar el owner de la empresa default
            DB::table('companies')
                ->where('id', $defaultCompanyId)
                ->update(['owner_id' => $firstUser->id]);
        }

        // 6. Crear suscripción Enterprise para la empresa default (sin fecha de vencimiento)
        $enterprisePackage = DB::table('packages')->where('slug', 'enterprise')->first();
        if ($enterprisePackage && $firstUser) {
            DB::table('subscriptions')->insert([
                'company_id' => $defaultCompanyId,
                'package_id' => $enterprisePackage->id,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addYears(100), // Prácticamente sin vencimiento
                'status' => 'active',
                'payment_method' => 'transfer',
                'auto_renew' => false,
                'notes' => 'Suscripción inicial del sistema',
                'created_by' => $firstUser->id,
                'approved_by' => $firstUser->id,
                'approved_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('Datos iniciales creados correctamente.');
        $this->command->info("Empresa default ID: {$defaultCompanyId}");
        $this->command->info("Categoría default ID: {$defaultCategoryId}");
        if ($firstUser) {
            $this->command->info("Usuario {$firstUser->name} configurado como super_admin");
        }
    }
}
