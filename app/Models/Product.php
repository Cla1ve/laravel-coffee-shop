<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'stock', 'image', 'is_active'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // slug из названия; если такой уже есть, добавляем номер: kofe, kofe-2, kofe-3
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-', 'ru') ?: 'tovar';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ссылка на картинку, если её нет, показываем заглушку
    public function getImageUrlAttribute(): string
    {
        return asset($this->image ?: 'images/no-photo.svg');
    }

    // только активные товары, их видят покупатели
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
