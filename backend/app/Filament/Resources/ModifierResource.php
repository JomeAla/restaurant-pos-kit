<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModifierResource\Pages;
use App\Models\Modifier;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static ?string $navigationGroup = 'Menu';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    Select::make('type')->options(['single' => 'Single Select', 'multi' => 'Multi Select'])->required(),
                    Toggle::make('is_required')->default(false),
                    TextInput::make('min_selection')->integer()->minValue(0)->default(0),
                    TextInput::make('max_selection')->integer()->minValue(0)->default(0),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->badge(),
                IconColumn::make('is_required')->boolean()->sortable(),
                TextColumn::make('options_count')->counts('options')->label('Options'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModifiers::route('/'),
            'create' => Pages\CreateModifier::route('/create'),
            'edit' => Pages\EditModifier::route('/{record}/edit'),
        ];
    }
}
