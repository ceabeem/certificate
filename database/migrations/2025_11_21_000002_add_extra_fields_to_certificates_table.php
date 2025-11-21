<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('grade')->nullable()->after('course');
            $table->string('certificate_code')->nullable()->unique()->after('grade');
            $table->string('issuer_org_name')->nullable()->after('issuer_id');
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['grade', 'certificate_code', 'issuer_org_name']);
        });
    }
};
