<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            
            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('is_read');
            }
            
            if (!Schema::hasColumn('notifications', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('priority');
            }
            
            // Add index for better performance
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['user_id', 'is_read']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['user_id', 'type']);
            $table->dropIndex(['expires_at']);
            
            // Drop columns
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }
            
            if (Schema::hasColumn('notifications', 'priority')) {
                $table->dropColumn('priority');
            }
            
            if (Schema::hasColumn('notifications', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
}