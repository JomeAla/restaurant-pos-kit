<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\MenuItem;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationGroup = 'Menu';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Select::make('category_id')->relationship('category', 'name')->required(),
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('slug')->required()->alphaDash()->maxLength(255)->unique(ignoreRecord: true),
                    Textarea::make('description'),
                    TextInput::make('price')->required()->numeric()->minValue(0)->prefix('$'),
                    TextInput::make('cost')->numeric()->minValue(0)->prefix('$'),
                    TextInput::make('image')->maxLength(255),
                    TextInput::make('tax_rate')->numeric()->minValue(0)->maxValue(100)->suffix('%'),
                    Toggle::make('is_active')->default(true),
                    Toggle::make('is_available')->default(true),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('price')->money('usd')->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                IconColumn::make('is_available')->boolean()->sortable(),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
