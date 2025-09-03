<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('document_name');
            $table->string('document_link'); // Link to Google Drive
            $table->string('category'); // Kategori dokumen
            $table->text('description')->nullable();
            $table->date('submission_date');
            $table->unsignedBigInteger('submitted_by'); // User ID yang submit
            $table->unsignedBigInteger('approver_id'); // Partner/approver ID
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'revision_required'])->default('submitted');
            $table->text('rejection_reason')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->datetime('rejected_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Tabel untuk multiple contributors (yang mengerjakan dokumen)
        Schema::create('document_contributors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_review_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->nullable(); // Role dalam pengerjaan dokumen
            $table->timestamps();

            $table->foreign('document_review_id')->references('id')->on('document_reviews')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Tabel untuk comments/feedback
        Schema::create('document_review_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_review_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->enum('type', ['general', 'approval', 'rejection', 'revision'])->default('general');
            $table->timestamps();

            $table->foreign('document_review_id')->references('id')->on('document_reviews')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Tabel untuk tracking history/log
        Schema::create('document_review_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_review_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // submitted, reviewed, approved, rejected, etc.
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('document_review_id')->references('id')->on('document_reviews')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_review_logs');
        Schema::dropIfExists('document_review_comments');
        Schema::dropIfExists('document_contributors');
        Schema::dropIfExists('document_reviews');
    }
}
