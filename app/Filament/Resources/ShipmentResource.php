<?php

namespace App\Filament\Resources;

use App\Models\Shipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ShipmentResource\Pages;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Transactions';

    public static function canCreate(): bool
    {
        return false; // Admin tidak bisa buat shipment baru manual
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('order.order_number')->label('Order'),
            Tables\Columns\TextColumn::make('courier')->label('Kurir'),
            Tables\Columns\TextColumn::make('service')->label('Service'),
            Tables\Columns\TextColumn::make('cost')->label('Ongkir'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'warning' => 'processing',
                    'info' => 'on_delivery',
                    'success' => 'delivered',
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }
}
