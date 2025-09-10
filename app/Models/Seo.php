<?php

namespace App\Models;

use Database\Factories\SeoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
