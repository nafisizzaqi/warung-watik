<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payments'; // Order::payments()

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('transaction_id')->label('Transaction ID')->sortable(),
                Tables\Columns\TextColumn::make('payment_type')->label('Payment Method')->sortable(),
                Tables\Columns\TextColumn::make('transaction_status')
                    ->badge()
                    ->colors([
                        'success' => 'success',   // bisa disesuaikan logikanya
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_amount')->label('Amount')->money('idr')->sortable(),
                Tables\Columns\TextColumn::make('fraud_status')->sortable(),
                Tables\Columns\TextColumn::make('transaction_time')->label('Transaction Time')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([]) // disable create
            ->actions([]); // disable edit/delete
    }
}
