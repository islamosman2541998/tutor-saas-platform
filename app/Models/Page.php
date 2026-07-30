<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title', 'content', 'status', 'show_in_navbar', 'show_in_footer',
    'sort_order', 'meta_title', 'meta_description',
])]
class Page extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'show_in_navbar' => 'boolean',
            'show_in_footer' => 'boolean',
        ];
    }

    public function statusLabel(): string
    {
        return $this->status === 'published' ? 'منشورة' : 'مسودة';
    }

    public function scopePublicly($query)
    {
        return $query->where('status', 'published');
    }
}
