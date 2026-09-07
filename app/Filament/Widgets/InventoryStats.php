<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = Inventory::count();
        $inStock = Inventory::whereColumn('stock_quantity', '>', 'reorder_level')->count();
        $lowStock = Inventory::whereColumn('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'reorder_level')->count();
        $outOfStock = Inventory::where('stock_quantity', '<=', 0)->count();
        $totalPieces = (int) Inventory::sum('stock_quantity');

        return [
            Stat::make('Total SKUs (per store)', $total)
                ->description('Inventory entries across all stores')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('gray'),
            Stat::make('In Stock', $inStock)
                ->description($totalPieces.' pieces total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Low Stock', $lowStock)
                ->description('At or below reorder level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make('Out of Stock', $outOfStock)
                ->description('Zero units available')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}