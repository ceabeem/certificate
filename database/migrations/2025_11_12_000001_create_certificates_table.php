<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_name');
            $table->string('student_wallet')->nullable();
            $table->string('course')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('sha256_hash')->unique();
            $table->string('ipfs_cid')->nullable();
            $table->string('blockchain_tx')->nullable();
            $table->string('blockchain_payload_file')->nullable();
            $table->unsignedBigInteger('issuer_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};
