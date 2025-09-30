<?php

namespace Modules\Feishu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Feishu\Database\Factories\FeishuApplicationFactory;

/**
 * @property int $id
 * @property string $name 应用名称
 * @property string $app_id App ID
 * @property string $app_secret App Secret
 * @property string $icon 应用图标
 * @property string $remark 备注
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @property int|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeishuApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeishuApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeishuApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeishuApplication where()
 * @mixin \Eloquent
 */
class FeishuApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        
    ];

    // protected static function newFactory(): FeishuApplicationFactory
    // {
    //     // return FeishuApplicationFactory::new();
    // }
}
