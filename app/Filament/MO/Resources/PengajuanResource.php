<?php

namespace App\Filament\MO\Resources;

use App\Filament\MO\Resources\PengajuanResource\Pages;
use App\Filament\MO\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\EditableTextColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\PengajuanMetadata;
use App\Models\jenisPengajuan;
use App\Models\formfield as FormField;
class PengajuanResource extends Resource
{
    public static function canCreate(): bool { return false; }
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }
     public static function getMetadataFields($jenisPengajuanId)
    {
        if (!$jenisPengajuanId) return []; // Prevents null error

        // Retrieve the field names for the selected JenisPengajuan from the database
        $formFields = FormField::where('jenis_pengajuan_id', $jenisPengajuanId)->get();

        // Collect the field names and return as an array
        return $formFields->map(fn ($field) => ['field_name' => $field->field_name, 'field_id' => $field->id]);
    }
    public static function renderMetadataFields($jenisPengajuanId)
    {
        if (!$jenisPengajuanId) return []; // Return empty if no JenisPengajuan is selected

        $fields = self::getMetadataFields($jenisPengajuanId);
        return array_map(function ($field){
            return Forms\Components\TextInput::make("metadata.{$field['field_name']}")
                ->label(ucwords(str_replace('_', ' ', $field['field_name'])))
                ->required();  // Render text inputs for metadata fields dynamically
        }, $fields->toArray());
    }
    public static function retrievePengajuanRecord($pengajuanId)
    {
        // Retrieve the Pengajuan record by its ID
        $pengajuan = Pengajuan::find($pengajuanId);

        // Check if Pengajuan record is found
        if (!$pengajuan) {
            // Handle the case where the record is not found (return null or handle as needed)
            return null;
        }

        // Retrieve the metadata associated with the Pengajuan record
        $metadata = PengajuanMetadata::where('pengajuan_id', $pengajuan->pengajuan_id)->get();

        // Map the metadata to include form field details
        $metadatas = $metadata->map(function ($item) use ($pengajuan) {
            // Retrieve the FormField based on the form_field_id and jenis_pengajuan_id
            $field = FormField::where('jenis_pengajuan_id', $pengajuan->jenis_pengajuan_id)
                            ->where('id', $item->form_field_id)
                            ->first(); // Using `first()` because we expect only one result

            // Return the updated metadata with field details
            return [
                'form_field_id' => $item->form_field_id,
                'field_value' => $item->field_value,
                'pengajuan_id' => $item->pengajuan_id,
                'jenis_pengajuan_id' => $pengajuan->jenis_pengajuan_id,
                'field_name' => $field ? $field->field_name : null,  // Safely check if field exists
                'field_id' => $field ? $field->id : null,  // Safely check if field exists
            ];
        });

        // Return the final array with metadata and field details
        return $metadatas->toArray();
    }
    public static function getEditFormFields($pengajuanId){
        $pengajuan = Pengajuan::find($pengajuanId);
        if (!$pengajuan) {
            // Handle the case where the record is not found (return null or handle as needed)
            return null;
        }
        $metadata = self::retrievePengajuanRecord($pengajuanId);
        return array_map(function ($item) {
            return Forms\Components\Placeholder::make("metadata.{$item['field_name']}")
                ->label(ucwords(str_replace('_', ' ', $item['field_name'])))
                ->content(function () use ($item) {
                    return $item['field_value'];
                });
        }, $metadata);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('nrp')->label('NRP Mahasiswa')->sortable(),
                TextColumn::make('jenisPengajuan.jenis_pengajuan_name')->label('Jenis Pengajuan')->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('metadata')
                        ->label('Metadata Fields')
                        ->formatStateUsing(function ($state, $record) {
                            return $record->metadata->map(function ($item) {
                                $fieldName = FormField::where('id', $item->form_field_id)->value('field_name');
                                return $fieldName . ': ' . $item->field_value;
                            })->implode(', ');
                        }),
            ])->modifyQueryUsing(function (Builder $query){
                    $prodi_id = auth::User()->prodi_id;
                    return $query->whereHas('user', function ($query) use ($prodi_id) {
                        $query->where('prodi_id', $prodi_id)->where('status','approved');
                    });
                })
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('ViewMetadata')
                ->label('View Metadata')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->modalHeading('View Metadata')
                ->modalWidth('lg')
                // Define the form using a Closure, passing the $record
                ->form(fn (Pengajuan $record): array => [
                        Select::make('jenis_pengajuan_id')
                            ->label('Jenis Pengajuan')
                            ->options(JenisPengajuan::pluck('jenis_pengajuan_name', 'jenis_pengajuan_id')->toArray() ?? [])
                            ->required()
                            ->reactive()  // This will trigger when the user selects a different option
                            ->afterStateUpdated(function ($state, callable $set) {
                                // When the 'jenis_pengajuan_id' is updated, populate 'metadata' fields
                                $set('metadata', self::getMetadataFields($state));
                            })
                            ->columnSpan('full')
                            ->default(fn ($record) => $record->jenis_pengajuan_id)  // Retrieve the current data from the record (editing scenario)
                            ->disabled(), 
                        Forms\Components\Group::make()
                        ->schema(fn($get) => self::getEditFormFields($record->pengajuan_id))
                        ->columnSpan('full'),
                        ])
                        ->modalCancelAction(false)
                        ->modalSubmitAction(false),
                Tables\Actions\Action::make('FileUpload')
                    ->label('Upload PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->modalHeading('Upload PDF')
                    ->modalWidth('lg')
                    // Define the form using Closure
                    ->form(function () {
                        return [
                            FileUpload::make('file_hasil_pengajuan') // Define the file upload field
                                ->label('Select PDF File')
                                ->disk('public')
                                ->directory('pdfs')
                                ->acceptedFileTypes(['application/pdf']) // Only PDF files
                                ->required() // Optional, can be removed if not required
                        ];
                    })
                    // Handle the form action for file upload
                    ->action(function (Pengajuan $record, array $data) {
                         if (isset($data['file_hasil_pengajuan']) ) {
                            // Save the relative file path (without the 'storage' part) in the database
                            $record->update(['file_hasil_pengajuan' => $data['file_hasil_pengajuan']]);
                            return $record;
                        } else {
                            // If no file was uploaded, you can log an error or handle accordingly
                            dd('No file uploaded or invalid file type.');
                        }
                    })
                    ->disabled(fn (Pengajuan $record) => $record->file_hasil_pengajuan) 
                    
            ])
            ->bulkActions([
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
