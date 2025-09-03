<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DocumentReview;
use App\Models\DocumentReviewCategory;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add category_id column
        Schema::table('document_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('document_link');
            $table->foreign('category_id')->references('id')->on('document_review_categories')->onDelete('set null');
            $table->index('category_id');
        });

        // Remove old category column (optional - you might want to keep it for backup)
        Schema::table('document_reviews', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_reviews', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};