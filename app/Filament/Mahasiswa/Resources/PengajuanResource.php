<?php
namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\PengajuanResource\Pages;
use App\Models\Pengajuan;
use App\Models\PengajuanMetadata;
use App\Models\JenisPengajuan;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\EditableTextColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\formfield as FormField;


class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    public static function getPluralLabel(): string
        {
            return 'Pengajuan'; // Custom label for the menu
        }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                Select::make('jenis_pengajuan_id')
                    ->label('Jenis Pengajuan')
                    ->options(JenisPengajuan::pluck('jenis_pengajuan_name', 'jenis_pengajuan_id')->toArray() ?? [])
                    ->required()
                    ->reactive()
                    ->disabledOn('edit')
                    ->afterStateUpdated(fn ($state, callable $set) => 
                        $set('metadata', self::getMetadataFields($state))
                    )
                    ->columnSpan('full'),
                Forms\Components\Fieldset::make('pengajuan_metadata')
                    ->label('Pengajuan Metadata')
                    ->schema(fn($get) => self::renderMetadataFields($get('jenis_pengajuan_id')))
                    ->columnSpan('full')
                    ->hiddenOn('View'),
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
    public static function table(Tables\Table $table): Tables\Table
    {
        $userNrp = Auth::user()->nrp;
        return $table
            ->columns([
                TextColumn::make('pengajuan_id')
                ->label ('Pengajuan Id')
                ->sortable(),
                TextColumn::make('jenisPengajuan.jenis_pengajuan_name')
                ->label('Jenis Pengajuan')
                ->sortable(),
                TextColumn::make('status')->sortable(),
                TextColumn::make('metadata')
                        ->label('Metadata Fields')
                        ->formatStateUsing(function ($state, $record) {
                            return $record->metadata->map(function ($item) {
                                $fieldName = FormField::where('id', $item->form_field_id)->value('field_name');
                                return $fieldName . ': ' . $item->field_value;
                            })->implode(', ');
                        })
                ])
                ->modifyQueryUsing(function (Builder $query){
                    $nrp = auth()->id();
                    $query->where('nrp',$nrp);
                    return $query;
                })
            ->actions([
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->color('primary')
                    ->url(fn (Pengajuan $record) => asset('storage/' . $record->file_hasil_pengajuan)) // Generate the URL for the PDF file
                    ->openUrlInNewTab()
                    ->disabled(fn (Pengajuan $record) => !$record->file_hasil_pengajuan)
                    ->hidden(fn(Pengajuan $record) => $record->status === 'pending'),
                Tables\Actions\ViewAction::make()
                ->form(fn (Pengajuan $record): array => [
                        Select::make('jenis_pengajuan_id')
                            ->label('Jenis Pengajuan')
                            ->options(JenisPengajuan::pluck('jenis_pengajuan_name', 'jenis_pengajuan_id')->toArray() ?? [])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => 
                                $set('metadata', self::getMetadataFields($state))
                            )
                            ->disabledOn('edit')
                            ->columnSpan('full'),
                        Forms\Components\Group::make()
                        ->schema(fn($get) => self::getEditFormFields($record->pengajuan_id))
                        ->columnSpan('full'),
                    ]),
                Tables\Actions\EditAction::make()
                    ->form(fn (Pengajuan $record): array => [
                        Select::make('jenis_pengajuan_id')
                            ->label('Jenis Pengajuan')
                            ->options(JenisPengajuan::pluck('jenis_pengajuan_name', 'jenis_pengajuan_id')->toArray() ?? [])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => 
                                $set('metadata', self::getMetadataFields($state))
                            )
                            ->disabledOn('edit')
                            ->columnSpan('full'),
                        Forms\Components\Group::make()
                        ->schema(fn($get) => self::getEditFormFields($record->pengajuan_id))
                        ->columnSpan('full'),
                        Forms\Components\Fieldset::make('pengajuan_metadata')
                            ->label('Pengajuan Metadata')
                            ->schema(fn($get) => self::renderMetadataFields($get('jenis_pengajuan_id')))
                            ->columnSpan('full'),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        if (isset($data['metadata'])) {
                            foreach ($data['metadata'] as $field => $value) {
                                $form_field_id = FormField::where('jenis_pengajuan_id',$record->jenis_pengajuan_id)
                                                        ->where('field_name', $field)
                                                        ->value('id');
                                $metadata = PengajuanMetadata::where('pengajuan_id', $record->pengajuan_id)
                                            ->where('form_field_id', $form_field_id)
                                            ->first();
                                $metadata->update(
                                    ['field_value' => $value]
                                );
                            }
                        }
                        return $record;
                    })
                    ->hidden(fn (Pengajuan $record): bool => in_array($record->status, ['approved', 'rejected'])),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Pengajuan $record): bool => in_array($record->status, ['approved', 'rejected']))
                    ->action(function (Pengajuan $record) {
                        $record->delete();
                    })
            ])
            
            ;
    }                                                                                                           


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
        ];
    }
}
