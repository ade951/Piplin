<?php

/**
 * 发布版本列表
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Piplin\Models\PublishVersions;

class CreatePublishVersionsTable extends Migration
{
    private $tableName = 'publish_versions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->string('version_name'); //版本号
            $table->string('version_hash'); //版本哈希
            $table->string('description'); //更新信息描述
            $table->tinyInteger('status')->default(PublishVersions::PENDING);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
