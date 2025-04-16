<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Prodi;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Forms\Components\TextInput::make('nrp')
                    ->label('NRP/NIP')
                    ->required()
                    ->unique(User::class, 'nrp', ignoreRecord: true) // Ignore unique check for the same record
                    ->disabled(fn ($record) => $record?->exists) // Read-only on edit
                    ->dehydrated(fn ($record) => !$record?->exists), // Prevent sending value on edit
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->label('Assign Roles')
                    ->visible(fn () => auth()->user()?->can('manage_roles')),
                Forms\Components\Select::make('prodi_id')
                    ->label('Study Program')
                    ->options(
                        Prodi::all()->pluck('prodi_name', 'prodi_id')
                    )
                    ->required(),
                Forms\Components\Select::make('isDosen')
                    ->label('Jabatan')
                    ->options([
                        '1' => 'Dosen',
                        '0' => 'Mahasiswa',
                    ])
                    ->default('0')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('nrp')
                ->label('NRP/NIP')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                ->label('Role')
                ->searchable(),
                Tables\Columns\TextColumn::make('prodi.prodi_name')
                ->label('Program Studi')
                ->searchable(),
                Tables\Columns\TextColumn::make('isDosen')
                    ->label('Jabatan')
                    ->formatStateUsing(function ($state, $record) {
                        // Check if the user is a Dosen
                        if ($record->isDosen && $record->hasRole('admin')) {
                            return 'Admin';
                        } elseif ($record->isDosen) {
                            return 'Dosen';
                        }
                        // Default to Mahasiswa if neither Dosen nor Admin
                        return 'Mahasiswa';
                    }), 
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('isDosen')
                    ->label('Dosen')
                    ->query(fn (Builder $query): Builder => $query->where('isDosen', 1)),
                Tables\Filters\Filter::make('mahasiswa')
                    ->label('Mahasiswa')
                    ->query(fn (Builder $query): Builder => $query->where('isDosen', 0)),
                Tables\Filters\Filter::make('admin')
                    ->label('Admin')
                    ->query(fn (Builder $query): Builder => $query->whereHas('roles', function ($q) {
                        $q->where('name', 'admin');
                    })),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
