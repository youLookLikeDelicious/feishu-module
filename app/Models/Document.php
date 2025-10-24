<?php

namespace Modules\Feishu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Feishu\Database\Factories\DocumentFactory;

/**
 * @property int $id
 * @property string $document_id
 * @property \Illuminate\Database\Eloquent\Collection<int, Tag> $tags
 * @property \Illuminate\Database\Eloquent\Collection<int, DocumentSyncLog> $syncLogs
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @property int|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document where()
 * @mixin \Eloquent
 */
class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
    ];

    // protected static function newFactory(): DocumentFactory
    // {
    //     // return DocumentFactory::new();
    // }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tag', 'document_id', 'tag_id');
    }

    public function syncLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentSyncLog::class);
    }
}
