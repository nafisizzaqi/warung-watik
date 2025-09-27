<?php

namespace App\Filament\Resources;

use App\Models\CartItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\CartItemResource\Pages;

class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Shop';

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('cart.user.name')->label('User'),
            Tables\Columns\TextColumn::make('product.name')->label('Product'),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('price'),
            Tables\Columns\TextColumn::make('subtotal'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItems::route('/'),
        ];
    }
}
