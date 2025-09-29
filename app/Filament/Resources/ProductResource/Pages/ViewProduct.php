<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3) // bikin grid 3 kolom
                    ->schema([
                        Infolists\Components\ImageEntry::make('image')
                            ->disk('public')
                            ->height(250)
                            ->width(250)
                            ->columnSpan(1)
                            ->extraImgAttributes(['class' => 'rounded-lg object-cover border shadow'])
                            ->label('Product Image'),

                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('name')
                                ->label('Product Name')
                                ->size('lg')
                                ->weight('bold'),

                            Infolists\Components\TextEntry::make('slug')
                                ->label('Slug'),

                            Infolists\Components\TextEntry::make('category.name')
                                ->label('Category'),

                            Infolists\Components\TextEntry::make('price')
                                ->money('idr', true)
                                ->label('Price'),

                            Infolists\Components\TextEntry::make('stock')
                                ->label('Stock'),

                            Infolists\Components\TextEntry::make('created_at')
                                ->dateTime('d M Y H:i')
                                ->label('Created At'),
                        ])->columnSpan(2),
                    ]),

                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
