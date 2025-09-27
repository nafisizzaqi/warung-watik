<?php

namespace App\Filament\Resources\CartResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items'; 

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('subtotal'),
            ])
            ->headerActions([]) 
            ->actions([]);
    }
}
