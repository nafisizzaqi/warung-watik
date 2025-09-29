<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Customer;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\CustomerResource\Pages;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Customers';
    protected static ?string $pluralModelLabel = 'Customers';
    protected static ?string $modelLabel = 'Customer';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\ImageColumn::make('image')->label('Profile')->rounded()->disk('public'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Registered')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
