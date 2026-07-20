<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\DowntimeCategory;
use App\Models\DowntimeReason;
use App\Models\Machine;
use App\Models\Product;
use App\Models\ProductionDowntime;
use App\Models\ProductionEntry;
use App\Models\ProductionLine;
use App\Models\ProductionPlan;
use App\Models\Shift;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────
        // 1. USER ACCOUNTS
        // ─────────────────────────────────────────
        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name'      => 'MES Administrator',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'supervisor@example.com'], [
            'name'      => 'MES Supervisor',
            'password'  => Hash::make('password'),
            'role'      => 'supervisor',
            'is_active' => true,
        ]);

        $operator = User::updateOrCreate(['email' => 'operator@example.com'], [
            'name'      => 'MES Operator',
            'password'  => Hash::make('password'),
            'role'      => 'operator',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'test@example.com'], [
            'name'      => 'Test Admin',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // ✅ Compte RH
        User::updateOrCreate(['email' => 'rh@example.com'], [
            'name'      => 'Responsable RH',
            'password'  => Hash::make('password'),
            'role'      => 'rh',
            'is_active' => true,
        ]);

        // ─────────────────────────────────────────
        // 2. ZONES
        // ─────────────────────────────────────────
        $zonesData = [
            ['code' => 'ZA', 'name' => 'Zone A - Assemblage',      'description' => "Zone principale d'assemblage",          'is_active' => true],
            ['code' => 'ZB', 'name' => 'Zone B - Conditionnement',  'description' => 'Zone de conditionnement et emballage',   'is_active' => true],
            ['code' => 'ZC', 'name' => 'Zone C - Contrôle Qualité', 'description' => 'Zone de contrôle et inspection qualité', 'is_active' => true],
        ];

        $createdZones = [];
        foreach ($zonesData as $zone) {
            $createdZones[] = Zone::updateOrCreate(['code' => $zone['code']], $zone);
        }

        // ─────────────────────────────────────────
        // 3. PRODUCTION LINES
        // ─────────────────────────────────────────
        $linesData = [
            ['zone_id' => $createdZones[0]->id, 'code' => 'LA1', 'name' => 'Ligne A1 - Montage Cadres',    'description' => 'Montage des cadres et châssis',          'is_active' => true],
            ['zone_id' => $createdZones[0]->id, 'code' => 'LA2', 'name' => 'Ligne A2 - Câblage',           'description' => 'Câblage électrique et électronique',     'is_active' => true],
            ['zone_id' => $createdZones[1]->id, 'code' => 'LB1', 'name' => 'Ligne B1 - Emballage',         'description' => 'Emballage automatisé des produits finis', 'is_active' => true],
            ['zone_id' => $createdZones[1]->id, 'code' => 'LB2', 'name' => 'Ligne B2 - Palettisation',     'description' => 'Palettisation et préparation expédition', 'is_active' => true],
            ['zone_id' => $createdZones[2]->id, 'code' => 'LC1', 'name' => 'Ligne C1 - Inspection Finale', 'description' => 'Contrôle qualité et tests finaux',        'is_active' => true],
        ];

        $createdLines = [];
        foreach ($linesData as $line) {
            $createdLines[] = ProductionLine::updateOrCreate(['code' => $line['code']], $line);
        }

        // Update operator's production line
        $operator->update(['production_line_id' => $createdLines[0]->id]);

        // ─────────────────────────────────────────
        // 4. MACHINES
        // ─────────────────────────────────────────
        $machinesData = [
            ['production_line_id' => $createdLines[0]->id, 'code' => 'M-A1-01', 'name' => 'Robot Soudure R1',        'line' => 'LA1', 'description' => 'Robot de soudure automatique',        'is_active' => true],
            ['production_line_id' => $createdLines[0]->id, 'code' => 'M-A1-02', 'name' => 'Presse Hydraulique P1',   'line' => 'LA1', 'description' => 'Presse 50T pour formage pièces',      'is_active' => true],
            ['production_line_id' => $createdLines[0]->id, 'code' => 'M-A1-03', 'name' => 'Convoyeur C1',            'line' => 'LA1', 'description' => 'Convoyeur principal ligne A1',         'is_active' => true],
            ['production_line_id' => $createdLines[1]->id, 'code' => 'M-A2-01', 'name' => 'Poste Câblage PC-01',    'line' => 'LA2', 'description' => 'Poste de câblage manuel assisté',     'is_active' => true],
            ['production_line_id' => $createdLines[1]->id, 'code' => 'M-A2-02', 'name' => 'Testeur Électrique TE-1', 'line' => 'LA2', 'description' => 'Banc de test électrique automatique', 'is_active' => true],
            ['production_line_id' => $createdLines[2]->id, 'code' => 'M-B1-01', 'name' => 'Thermoformeuse TF-1',    'line' => 'LB1', 'description' => 'Machine thermoformage emballages',    'is_active' => true],
            ['production_line_id' => $createdLines[2]->id, 'code' => 'M-B1-02', 'name' => 'Encaisseuse E1',          'line' => 'LB1', 'description' => 'Encaisseuse automatique cartons',     'is_active' => true],
            ['production_line_id' => $createdLines[3]->id, 'code' => 'M-B2-01', 'name' => 'Palettiseur Robot PR-1', 'line' => 'LB2', 'description' => 'Robot palettiseur 6 axes',            'is_active' => true],
            ['production_line_id' => $createdLines[4]->id, 'code' => 'M-C1-01', 'name' => 'Banc de Test BT-1',       'line' => 'LC1', 'description' => 'Banc de test qualité multi-paramètres', 'is_active' => true],
            ['production_line_id' => $createdLines[4]->id, 'code' => 'M-C1-02', 'name' => 'Caméra Vision CV-1',      'line' => 'LC1', 'description' => 'Système vision artificielle contrôle', 'is_active' => true],
        ];

        $createdMachines = [];
        foreach ($machinesData as $machine) {
            $createdMachines[] = Machine::updateOrCreate(['code' => $machine['code']], $machine);
        }

        // ─────────────────────────────────────────
        // 5. PRODUCTS
        // ─────────────────────────────────────────
        $productsData = [
            ['code' => 'PRD-001', 'name' => 'Tableau de Bord TDB-X200', 'unit' => 'PCS', 'standard_qty_per_hour' => 45,  'is_active' => true],
            ['code' => 'PRD-002', 'name' => 'Module Câblage MC-500',    'unit' => 'PCS', 'standard_qty_per_hour' => 30,  'is_active' => true],
            ['code' => 'PRD-003', 'name' => 'Faisceau Électrique FE-A', 'unit' => 'PCS', 'standard_qty_per_hour' => 60,  'is_active' => true],
            ['code' => 'PRD-004', 'name' => 'Boîtier Plastique BP-100', 'unit' => 'PCS', 'standard_qty_per_hour' => 120, 'is_active' => true],
            ['code' => 'PRD-005', 'name' => 'Connecteur CX-50',         'unit' => 'PCS', 'standard_qty_per_hour' => 200, 'is_active' => true],
            ['code' => 'PRD-006', 'name' => 'Capteur Température CT-1', 'unit' => 'PCS', 'standard_qty_per_hour' => 80,  'is_active' => true],
        ];

        $createdProducts = [];
        foreach ($productsData as $product) {
            $createdProducts[] = Product::updateOrCreate(['code' => $product['code']], $product);
        }

        // ─────────────────────────────────────────
        // 6. PRODUCTS ↔ LINES PIVOT
        // ─────────────────────────────────────────
        $createdLines[0]->products()->syncWithoutDetaching([
            $createdProducts[0]->id => ['standard_qty_per_hour' => 45,  'is_active' => true],
            $createdProducts[1]->id => ['standard_qty_per_hour' => 30,  'is_active' => true],
            $createdProducts[2]->id => ['standard_qty_per_hour' => 60,  'is_active' => true],
        ]);
        $createdLines[1]->products()->syncWithoutDetaching([
            $createdProducts[1]->id => ['standard_qty_per_hour' => 30,  'is_active' => true],
            $createdProducts[2]->id => ['standard_qty_per_hour' => 55,  'is_active' => true],
            $createdProducts[3]->id => ['standard_qty_per_hour' => 110, 'is_active' => true],
        ]);
        $createdLines[2]->products()->syncWithoutDetaching([
            $createdProducts[3]->id => ['standard_qty_per_hour' => 120, 'is_active' => true],
            $createdProducts[4]->id => ['standard_qty_per_hour' => 200, 'is_active' => true],
        ]);
        $createdLines[3]->products()->syncWithoutDetaching([
            $createdProducts[3]->id => ['standard_qty_per_hour' => 115, 'is_active' => true],
            $createdProducts[4]->id => ['standard_qty_per_hour' => 195, 'is_active' => true],
            $createdProducts[5]->id => ['standard_qty_per_hour' => 80,  'is_active' => true],
        ]);
        $createdLines[4]->products()->syncWithoutDetaching([
            $createdProducts[0]->id => ['standard_qty_per_hour' => 45, 'is_active' => true],
            $createdProducts[1]->id => ['standard_qty_per_hour' => 30, 'is_active' => true],
            $createdProducts[5]->id => ['standard_qty_per_hour' => 80, 'is_active' => true],
        ]);

        // ─────────────────────────────────────────
        // 7. SHIFTS
        // ─────────────────────────────────────────
        $shiftsData = [
            ['code' => 'MATIN', 'name' => 'Équipe Matin',      'start_time' => '06:00:00', 'end_time' => '14:00:00', 'is_active' => true],
            ['code' => 'APREM', 'name' => 'Équipe Après-midi', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'is_active' => true],
            ['code' => 'NUIT',  'name' => 'Équipe Nuit',       'start_time' => '22:00:00', 'end_time' => '06:00:00', 'is_active' => true],
        ];

        $createdShifts = [];
        foreach ($shiftsData as $shift) {
            $createdShifts[] = Shift::updateOrCreate(['code' => $shift['code']], $shift);
        }

        // ─────────────────────────────────────────
        // 8. DOWNTIME CATEGORIES & REASONS
        // ─────────────────────────────────────────
        $downtimeData = [
            ['category' => 'Mécanique',         'reasons' => ['Panne moteur', 'Rupture courroie', 'Blocage mécanique', 'Usure pièce']],
            ['category' => 'Électrique',         'reasons' => ['Coupure alimentation', 'Court-circuit', 'Défaut capteur', 'Problème API/PLC']],
            ['category' => 'Qualité / Process',  'reasons' => ['Non-conformité matière', 'Réglage machine requis', 'Changement de série']],
            ['category' => 'Organisationnel',    'reasons' => ['Manque opérateur', 'Attente matière première', 'Réunion / Formation', 'Pause réglementaire']],
        ];

        $allCategories = [];
        $allReasons    = [];

        foreach ($downtimeData as $item) {
            $category = DowntimeCategory::updateOrCreate(
                ['name' => $item['category']],
                ['name' => $item['category'], 'is_active' => true]
            );
            $allCategories[] = $category;
            foreach ($item['reasons'] as $reasonName) {
                $allReasons[] = DowntimeReason::updateOrCreate(
                    ['name' => $reasonName, 'downtime_category_id' => $category->id],
                    ['name' => $reasonName, 'downtime_category_id' => $category->id, 'is_active' => true]
                );
            }
        }

        // ─────────────────────────────────────────
        // 9. PRODUCTION PLANS + ENTRIES + DOWNTIMES
        // ─────────────────────────────────────────
        $planScenarios = [
            ['daysAgo' => 6, 'lineIdx' => 0, 'productIdx' => 0, 'shiftIdx' => 0, 'planned' => 360, 'actual' => 330, 'rejected' => 12, 'status' => 'completed'],
            ['daysAgo' => 6, 'lineIdx' => 1, 'productIdx' => 1, 'shiftIdx' => 1, 'planned' => 240, 'actual' => 210, 'rejected' => 8,  'status' => 'completed'],
            ['daysAgo' => 5, 'lineIdx' => 2, 'productIdx' => 3, 'shiftIdx' => 0, 'planned' => 960, 'actual' => 900, 'rejected' => 30, 'status' => 'completed'],
            ['daysAgo' => 5, 'lineIdx' => 0, 'productIdx' => 2, 'shiftIdx' => 2, 'planned' => 480, 'actual' => 450, 'rejected' => 15, 'status' => 'completed'],
            ['daysAgo' => 4, 'lineIdx' => 3, 'productIdx' => 4, 'shiftIdx' => 0, 'planned' => 1600,'actual' => 1500,'rejected' => 40, 'status' => 'completed'],
            ['daysAgo' => 3, 'lineIdx' => 1, 'productIdx' => 2, 'shiftIdx' => 1, 'planned' => 440, 'actual' => 400, 'rejected' => 20, 'status' => 'completed'],
            ['daysAgo' => 2, 'lineIdx' => 4, 'productIdx' => 5, 'shiftIdx' => 0, 'planned' => 640, 'actual' => 600, 'rejected' => 18, 'status' => 'completed'],
            ['daysAgo' => 1, 'lineIdx' => 0, 'productIdx' => 1, 'shiftIdx' => 0, 'planned' => 240, 'actual' => 220, 'rejected' => 10, 'status' => 'completed'],
            ['daysAgo' => 0, 'lineIdx' => 2, 'productIdx' => 3, 'shiftIdx' => 0, 'planned' => 960, 'actual' => 500, 'rejected' => 10, 'status' => 'in_progress'],
            ['daysAgo' => 0, 'lineIdx' => 1, 'productIdx' => 2, 'shiftIdx' => 1, 'planned' => 480, 'actual' => 0,   'rejected' => 0,  'status' => 'planned'],
        ];

        foreach ($planScenarios as $scenario) {
            $line    = $createdLines[$scenario['lineIdx']];
            $product = $createdProducts[$scenario['productIdx']];
            $shift   = $createdShifts[$scenario['shiftIdx']];
            $planDate = Carbon::today()->subDays($scenario['daysAgo']);

            $plan = ProductionPlan::create([
                'plan_date'           => $planDate->toDateString(),
                'zone_id'             => $line->zone_id,
                'production_line_id'  => $line->id,
                'shift_id'            => $shift->id,
                'product_id'          => $product->id,
                'hour_start'          => $shift->start_time,
                'hour_end'            => $shift->end_time,
                'planned_qty'         => $scenario['planned'],
                'target_oee'          => 85.00,
                'responsible'         => 'MES Supervisor',
                'notes'               => 'Plan généré automatiquement par le seeder de démonstration.',
                'status'              => $scenario['status'],
                'created_by'          => $admin->id,
            ]);

            // Only create entries for non-planned
            if ($scenario['status'] === 'planned') {
                continue;
            }

            $actualQty   = $scenario['actual'];
            $rejectedQty = $scenario['rejected'];
            $goodQty     = max(0, $actualQty - $rejectedQty);
            $stopMin     = rand(5, 30);
            $shiftHours  = 8;
            $shiftMin    = $shiftHours * 60;
            $availability = round((($shiftMin - $stopMin) / $shiftMin) * 100, 2);
            $performance  = $scenario['planned'] > 0
                ? round(($actualQty / $scenario['planned']) * 100, 2)
                : 0;
            $quality      = $actualQty > 0
                ? round(($goodQty / $actualQty) * 100, 2)
                : 100;
            $oee = round(($availability / 100) * ($performance / 100) * ($quality / 100) * 100, 2);

            $entryStatus = $scenario['status'] === 'completed' ? 'finished' : 'draft';
            $machine     = $createdMachines[array_rand($createdMachines)];

            $entry = ProductionEntry::create([
                'production_plan_id' => $plan->id,
                'zone_id'            => $line->zone_id,
                'production_line_id' => $line->id,
                'production_date'    => $planDate->toDateString(),
                'shift_id'           => $shift->id,
                'machine_id'         => $machine->id,
                'product_id'         => $product->id,
                'hour_start'         => $shift->start_time,
                'hour_end'           => $shift->end_time,
                'planned_qty'        => $scenario['planned'],
                'actual_qty'         => $actualQty,
                'rejected_qty'       => $rejectedQty,
                'chute_qty'          => rand(0, 5),
                'good_qty'           => $goodQty,
                'machine_status'     => 'running',
                'entry_status'       => $entryStatus,
                'stop_duration_min'  => $stopMin,
                'stops_count'        => rand(1, 3),
                'availability'       => $availability,
                'performance'        => min(100, $performance),
                'quality'            => $quality,
                'oee'                => min(100, $oee),
                'comment'            => 'Entrée de production - données de démonstration.',
                'completed_at'       => $entryStatus === 'finished' ? $planDate->setTime(22, 0) : null,
                'approved_by'        => $entryStatus === 'finished' ? $admin->id : null,
                'approved_at'        => $entryStatus === 'finished' ? $planDate->setTime(22, 30) : null,
                'created_by'         => $admin->id,
            ]);

            // Add 1-2 downtimes per entry
            $numDowntimes = rand(1, 2);
            for ($d = 0; $d < $numDowntimes; $d++) {
                $cat    = $allCategories[array_rand($allCategories)];
                $reason = $allReasons[array_rand($allReasons)];
                $downMin = rand(5, 20);
                $startAt = $planDate->copy()->setTime(8 + $d * 2, 0);
                $endAt   = $startAt->copy()->addMinutes($downMin);

                ProductionDowntime::create([
                    'production_plan_id'  => $plan->id,
                    'production_entry_id' => $entry->id,
                    'machine_id'          => $machine->id,
                    'started_at'          => $startAt,
                    'ended_at'            => $endAt,
                    'duration_min'        => $downMin,
                    'downtime_category_id'=> $cat->id,
                    'downtime_reason_id'  => $reason->id,
                    'comment'             => 'Arrêt machine enregistré - démonstration.',
                    'created_by'          => $admin->id,
                ]);
            }
        }

        // ─────────────────────────────────────────
        // 10. ABSENCES (demo data for RH)
        // ─────────────────────────────────────────
        $absencesData = [
            ['user_id' => $operator->id, 'date' => Carbon::today()->subDays(5)->toDateString(), 'type' => 'maladie',  'motif' => 'Arrêt maladie',           'statut' => 'approved', 'notes' => 'Certificat médical fourni'],
            ['user_id' => $operator->id, 'date' => Carbon::today()->subDays(2)->toDateString(), 'type' => 'retard',   'motif' => 'Problème de transport',   'statut' => 'approved', 'notes' => null],
            ['user_id' => $operator->id, 'date' => Carbon::today()->subDays(1)->toDateString(), 'type' => 'absence',  'motif' => 'Sans justification',      'statut' => 'pending',  'notes' => 'En attente de justificatif'],
            ['user_id' => $admin->id,    'date' => Carbon::today()->subDays(3)->toDateString(), 'type' => 'conge',    'motif' => 'Congé annuel',            'statut' => 'approved', 'notes' => 'Congé planifié'],
            ['user_id' => $operator->id, 'date' => Carbon::today()->toDateString(),             'type' => 'retard',   'motif' => 'Embouteillages',          'statut' => 'pending',  'notes' => null],
        ];

        foreach ($absencesData as $absence) {
            Absence::updateOrCreate(
                ['user_id' => $absence['user_id'], 'date' => $absence['date']],
                array_merge($absence, ['created_by' => $admin->id])
            );
        }

        // ─────────────────────────────────────────
        // DONE
        // ─────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅  Demo data seeded successfully!');
        $this->command->info('');
        $this->command->info('👤  User Accounts:');
        $this->command->info('    Admin:      admin@example.com      / password');
        $this->command->info('    Supervisor: supervisor@example.com / password');
        $this->command->info('    Operator:   operator@example.com   / password');
        $this->command->info('    RH:         rh@example.com         / password');
        $this->command->info('');
        $this->command->info('🏭  Master Data: 3 Zones, 5 Lines, 10 Machines, 6 Products, 3 Shifts');
        $this->command->info('📊  Production: 10 Plans, Entries with OEE, Downtimes');
        $this->command->info('📋  RH: 5 Absences demo records');
    }
}
