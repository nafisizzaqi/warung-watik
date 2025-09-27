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
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('tracking_number'),
            Forms\Components\Select::make('status')->options([
                'processing' => 'Processing',
                'on_delivery' => 'On Delivery',
                'delivered' => 'Delivered',
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('order.order_number')->label('Order'),
            Tables\Columns\TextColumn::make('courier'),
            Tables\Columns\TextColumn::make('service'),
            Tables\Columns\TextColumn::make('cost'),
            Tables\Columns\TextColumn::make('status'),
            Tables\Columns\TextColumn::make('tracking_number'),
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
