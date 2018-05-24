<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTasksTableAddBuildVariables extends Migration
{
    private $tableName = 'tasks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('vsign', 255)->default('')->comment('项目签名');
            $table->tinyInteger('is_encrypt')->default(0)->comment('是否加密');
            $table->string('domain_restriction', 255)->default('')->comment('加密域名限制');
            $table->string('php_version', 20)->default('')->comment('加密PHP版本');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('vsign');
            $table->dropColumn('is_encrypt');
            $table->dropColumn('domain_restriction');
            $table->dropColumn('php_version');
        });
    }
}
