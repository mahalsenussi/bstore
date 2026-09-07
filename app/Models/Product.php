<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function coverImage()
    {
        return $this->hasOne(ProductImage::class)->ofMany('sort_order', 'min');
    }

    public function getPriceFromAttribute()
    {
        return $this->variants()->where('status', true)->min('price');
    }

    public function getMaxPriceAttribute()
    {
        $compare = $this->variants()->where('status', true)
            ->whereNotNull('compare_at_price')
            ->max('compare_at_price');

        return $compare ?: $this->price_from;
    }

    public function getIsStockedAttribute(): bool
    {
        return $this->variants()->where('status', true)->get()
            ->contains(fn ($v) => $v->available_quantity > 0);
    }

    public function getCoverUrlAttribute(): ?string
    {
        $cover = $this->coverImage;

        return $cover ? \Illuminate\Support\Facades\Storage::disk('public')->url($cover->path) : null;
    }
}