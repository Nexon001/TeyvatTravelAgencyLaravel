<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'nation_name', 'description', 'hero_image'])]
class Nation extends Model
{
    /** The NATIONS table in the concept paper has no created_at / updated_at. */
    public $timestamps = false;

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }
}
