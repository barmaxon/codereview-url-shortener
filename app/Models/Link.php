<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string long_url
 */
class Link extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'link_tags');
    }
}
