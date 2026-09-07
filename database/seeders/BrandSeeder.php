<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Store;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $skechers = Brand::firstOrCreate(
            ['slug' => 'skechers'],
            [
                'name' => 'Skechers',
                'description' => 'Skechers designs and markets lifestyle and performance footwear for men, women and children.',
                'status' => true,
            ]
        );

        $okaid = Brand::firstOrCreate(
            ['slug' => 'okaid'],
            [
                'name' => 'Okaïdi',
                'description' => 'Okaïdi is an affordable, quality children\'s clothing brand that grows with your kids.',
                'status' => true,
            ]
        );

        Store::firstOrCreate(
            ['name' => 'Skechers - Cairo Festival City', 'brand_id' => $skechers->id],
            [
                'city' => 'Cairo',
                'address' => 'Cairo Festival City, New Cairo',
                'phone' => '+20 2 12345678',
                'email' => 'cairofc@skechers-eg.com',
                'status' => true,
            ]
        );

        Store::firstOrCreate(
            ['name' => 'Okaïdi - City Stars', 'brand_id' => $okaid->id],
            [
                'city' => 'Cairo',
                'address' => 'City Stars Mall, Nasr City',
                'phone' => '+20 2 87654321',
                'email' => 'citystars@okaid-eg.com',
                'status' => true,
            ]
        );
    }
}