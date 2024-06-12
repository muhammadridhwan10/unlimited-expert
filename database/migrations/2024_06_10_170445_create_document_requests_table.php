<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->nullable();
            $table->integer('approval')->nullable();
            $table->string('document_type')->nullable();
            $table->string('client_name')->nullable();
            $table->string('email_attention')->nullable();
            $table->string('name_attention')->nullable();
            $table->string('position_attention')->nullable();
            $table->string('service_type')->nullable();
            $table->string('period')->nullable();
            $table->string('termin1')->nullable();
            $table->string('termin2')->nullable();
            $table->string('termin3')->nullable();
            $table->string('fee')->nullable();
            $table->string('pph23')->nullable();
            $table->string('address')->nullable();
            $table->string('no_pic')->nullable();
            $table->string('file')->nullable();
            $table->string('file_feedback')->nullable();
            $table->text('note')->nullable();
            $table->string('status');
            $table->integer('created_by');
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
        Schema::dropIfExists('document_requests');
    }
}
