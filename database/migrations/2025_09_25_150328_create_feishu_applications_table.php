<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feishu_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->default('')->comment('应用名称');
            $table->string('app_id', 100)->default('')->comment('App ID');
            $table->string('app_secret', 100)->default('')->comment('App Secret');
            $table->string('icon')->default('')->comment('应用图标');
            $table->string('remark')->default('')->comment('备注');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(0)->comment('修改时间');
            $table->unsignedInteger('deleted_at')->nullable()->default(null)->comment('删除时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feishu_applications');
    }
};
