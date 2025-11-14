<?php

namespace App\Models;

use Database\Factories\SeoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property string meta_title
 * @property string meta_description
 * @property int post_id
 */
class Seo extends Model
{
    /** @use HasFactory<SeoFactory> */
    use HasFactory;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
