<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Alle Produkte'),
            'draft' => Tab::make('Entwürfe')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'active' => Tab::make('Aktiv')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),
            'archived' => Tab::make('Archiviert')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
        ];
    }
}
