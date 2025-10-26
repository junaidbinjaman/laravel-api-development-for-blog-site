<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property Collection|Post[] $posts;
 */
class Category extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
