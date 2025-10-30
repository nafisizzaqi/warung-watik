<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'shipments'; // Order::shipments()

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('courier')->label('Courier'),
                Tables\Columns\TextColumn::make('service')->label('Service'),
                Tables\Columns\TextColumn::make('cost')->money('idr'),
                Tables\Columns\TextColumn::make('etd')->label('Est. Delivery'),
                Tables\Columns\TextColumn::make('tracking_number')->label('Tracking No'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([]) // disable create
            ->actions([]); // disable edit/delete
    }
}
