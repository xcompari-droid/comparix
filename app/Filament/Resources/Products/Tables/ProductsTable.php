<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->label('Image')
                    ->square()
                    ->limit(1),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('brand')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('productType.name')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('mpn')
                    ->label('MPN')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                
                TextColumn::make('ean')
                    ->label('EAN')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                
                TextColumn::make('score')
                    ->numeric()
                    ->sortable()
                    ->suffix('/100')
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                
                TextColumn::make('offers_count')
                    ->counts('offers')
                    ->label('Offers')
                    ->badge(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_type_id')
                    ->relationship('productType', 'name')
                    ->label('Product Type')
                    ->preload(),
                
                SelectFilter::make('brand')
                    ->options(fn () => \App\Models\Product::distinct()->pluck('brand', 'brand')->toArray())
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

