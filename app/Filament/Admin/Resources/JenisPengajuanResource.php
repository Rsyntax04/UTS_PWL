<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JenisPengajuanResource\Pages;
use App\Filament\Admin\Resources\JenisPengajuanResource\RelationManagers;
use App\Models\JenisPengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Models\FormField;
use Filament\Tables\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;

class JenisPengajuanResource extends Resource
{
    protected static ?string $model = JenisPengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

        public static function getPluralLabel(): string
        {
            return 'Jenis Pengajuan'; // Custom label for the menu
        }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('jenis_pengajuan_name')
                    ->label('Jenis Pengajuan')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Fieldset::make('Form Fields')
                    ->schema([
                        Forms\Components\Repeater::make('form_fields') // Data key to store the input fields
                            ->label('Add Form Field')
                            ->schema([
                                Forms\Components\TextInput::make('field_name')
                                    ->label('Field Name')
                                    ->required()
                            ])
                            ->createItemButtonLabel('Add Field')
                            ->columnSpan('full')
                            ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_pengajuan_name')
                    ->label('Jenis Pengajuan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formfield.field_name')
                    ->label('Form Fields')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJenisPengajuans::route('/'),
            'create' => Pages\CreateJenisPengajuan::route('/create'),
        ];
    }
}
