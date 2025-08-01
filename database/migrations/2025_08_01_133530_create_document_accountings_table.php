<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentAccountingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_accountings', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->enum('document_type', ['rekening_koran', 'ebupot']);
            $table->enum('status', ['uploaded', 'processing', 'completed', 'failed'])->default('uploaded');
            $table->json('extracted_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('n8n_execution_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_accountings');
    }
}
