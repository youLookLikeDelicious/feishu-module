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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->unique();
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(0)->comment('修改时间');
            $table->unsignedInteger('deleted_at')->nullable()->default(null)->comment('删除时间');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary()->autoIncrement();
            $table->string('name')->unique();
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(0)->comment('修改时间');
        });

        Schema::create('document_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('document_id')->constrained('documents')->onDelete('cascade');
            $table->unsignedMediumInteger('tag_id')->constrained('tags')->onDelete('cascade');
        });

        Schema::create('document_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->index()->comment('文档ID');
            $table->unsignedInteger('synced_at')->default(0)->comment('同步时间');
            $table->unsignedTinyInteger('status')->default(1)->comment('同步状态 1 同步成功 2 同步失败 3 同步中');
            $table->string('platform', 45)->default('')->comment('同步平台');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->unsignedInteger('updated_at')->default(0)->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('document_sync_logs');
    }
};
