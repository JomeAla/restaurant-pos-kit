<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestaurantTableResource\Pages;
use App\Models\RestaurantTable;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RestaurantTableResource extends Resource
{
    protected static ?string $model = RestaurantTable::class;

    protected static ?string $navigationGroup = 'Tables';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('table_number')->required()->integer(),
                    TextInput::make('capacity')->required()->integer()->minValue(1),
                    TextInput::make('section')->maxLength(255),
                    Select::make('status')->options([
                        'free' => 'Free',
                        'occupied' => 'Occupied',
                        'reserved' => 'Reserved',
                        'dirty' => 'Dirty',
                    ])->default('free')->required(),
                    TextInput::make('pos_x')->numeric(),
                    TextInput::make('pos_y')->numeric(),
                    TextInput::make('width')->numeric(),
                    TextInput::make('height')->numeric(),
                    Select::make('shape')->options([
                        'rectangle' => 'Rectangle',
                        'circle' => 'Circle',
                    ])->default('rectangle')->required(),
                    Select::make('floor_plan_id')->relationship('floorPlan', 'name'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('table_number')->sortable(),
                TextColumn::make('capacity'),
                TextColumn::make('section')->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'free' => 'success',
                    'occupied' => 'danger',
                    'reserved' => 'warning',
                    'dirty' => 'gray',
                }),
                TextColumn::make('floorPlan.name'),
            ])
            ->defaultSort('table_number')
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurantTables::route('/'),
            'create' => Pages\CreateRestaurantTable::route('/create'),
            'edit' => Pages\EditRestaurantTable::route('/{record}/edit'),
        ];
    }
}
