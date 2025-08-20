<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataToPsychotestCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('psychotest_categories', function (Blueprint $table) {
            $table->boolean('is_job_specific')->default(false)->after('type');
            $table->json('target_job_keywords')->nullable()->after('is_job_specific');
        });

        Schema::table('psychotest_schedules', function (Blueprint $table) {
            $table->json('selected_categories')->nullable()->after('duration_minutes');
        });

        // Seeder untuk test category baru
        $this->seedNewCategories();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('psychotest_categories', function (Blueprint $table) {
            $table->dropColumn(['is_job_specific', 'target_job_keywords']);
        });

        Schema::table('psychotest_schedules', function (Blueprint $table) {
            $table->dropColumn('selected_categories');
        });
    }

    private function seedNewCategories()
    {
        // Insert Tes Bidang category
        DB::table('psychotest_categories')->insert([
            'name' => 'Tes Bidang (Auditor/Tax/Accounting)',
            'code' => 'field_test',
            'description' => 'Tes khusus untuk menguji kemampuan teknis bidang auditor, perpajakan, dan akuntansi',
            'type' => 'field_specific',
            'duration_minutes' => 15,
            'total_questions' => 20,
            'order' => 5,
            'is_job_specific' => true,
            'target_job_keywords' => json_encode(['auditor', 'audit', 'tax', 'taxation', 'accounting', 'akuntan', 'perpajakan']),
            'settings' => json_encode([
                'difficulty_levels' => ['easy', 'medium', 'hard'],
                'topics' => ['audit_procedures', 'tax_calculation', 'financial_accounting', 'internal_control']
            ]),
            'is_active' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert EPPS Test category
        DB::table('psychotest_categories')->insert([
            'name' => 'EPPS (Edward Personal Preference Schedule)',
            'code' => 'epps_test',
            'description' => 'Tes kepribadian untuk mengukur kebutuhan dan preferensi personal dalam bekerja',
            'type' => 'personality',
            'duration_minutes' => 60, // Biasanya EPPS membutuhkan waktu lebih lama
            'total_questions' => 100,
            'order' => 6,
            'is_job_specific' => false,
            'target_job_keywords' => null,
            'settings' => json_encode([
                'personality_dimensions' => [
                    'achievement', 'deference', 'order', 'exhibition', 'autonomy',
                    'affiliation', 'intraception', 'succorance', 'dominance',
                    'abasement', 'nurturance', 'change', 'endurance', 'heterosexuality', 'aggression'
                ],
                'scoring_method' => 'forced_choice',
                'show_progress' => true
            ]),
            'is_active' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
