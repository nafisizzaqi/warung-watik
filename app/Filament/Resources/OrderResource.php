<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\ShipmentRelationManager;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Shop';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Masuk',
                    'processing' => 'Diproses',
                    'ready' => 'Siap_ambil',
                    'success' => 'Selesai',
                    'cancel' => 'Batal',
                ])->required(),     
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id'),
            Tables\Columns\TextColumn::make('order_number'),
            Tables\Columns\TextColumn::make('customer.name')->label('User'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'primary' => 'masuk',
                    'warning' => 'diproses',
                    'success' => 'selesai',
                    'danger' => 'batal',
                ]),
            Tables\Columns\TextColumn::make('grand_total'),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            PaymentRelationManager::class,
            ShipmentRelationManager::class,
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
