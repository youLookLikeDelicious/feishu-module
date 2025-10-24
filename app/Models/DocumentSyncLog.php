<?php

namespace Modules\Feishu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Feishu\Database\Factories\DocumentSyncLogFactory;

/**
 * @property int $id
 * @property string $document_id 文档ID
 * @property int $synced_at 同步时间
 * @property int $status 同步状态 1 同步成功 2 同步失败 3 同步中
 * @property string $platform 同步平台
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentSyncLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentSyncLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentSyncLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentSyncLog where()
 * @mixin \Eloquent
 */
class DocumentSyncLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'synced_at',
        'status',
        'platform',
    ];

    // protected static function newFactory(): DocumentSyncLogFactory
    // {
    //     // return DocumentSyncLogFactory::new();
    // }
}
