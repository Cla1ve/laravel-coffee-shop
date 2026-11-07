<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // допустимые статусы и их названия для экрана
    public const STATUSES = [
        'new' => 'Новый',
        'processing' => 'В обработке',
        'shipped' => 'Отправлен',
        'done' => 'Выполнен',
        'cancelled' => 'Отменён',
    ];

    protected $fillable = ['user_id', 'status', 'total', 'customer_name', 'phone', 'address', 'comment'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
