<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_type_id')
                    ->relationship('productType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                
                TextInput::make('brand')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('mpn')
                    ->label('MPN')
                    ->maxLength(255)
                    ->helperText('Manufacturer Part Number'),
                
                TextInput::make('ean')
                    ->label('EAN')
                    ->maxLength(255)
                    ->helperText('European Article Number / Barcode'),
                
                TextInput::make('score')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('/100')
                    ->helperText('Product quality score (0-100)'),
                
                Textarea::make('short_desc')
                    ->label('Short Description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('images')
                    ->multiple()
                    ->image()
                    ->maxFiles(5)
                    ->columnSpanFull()
                    ->helperText('Upload product images (max 5)'),
            ])
            ->columns(3);
    }
}

