<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'student_name', 'student_image', 'content', 'rating', 'grade_or_group',
    'is_featured', 'is_active', 'sort_order',
])]
class Testimonial extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function studentImageUrl(): ?string
    {
        return $this->student_image ? Storage::disk('public')->url($this->student_image) : null;
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('is_active', true);
    }
}
