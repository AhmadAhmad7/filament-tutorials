<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Country;
use App\Models\Employee;
use App\Models\State;
// use Doctrine\DBAL\Query\QueryBuilder;
use Carbon\Carbon;
use Doctrine\DBAL\Schema\Column;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
// use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Schema;
use function Laravel\Prompts\multisearch;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup ='Employee Management';
    protected static ?string $recordTitleAttribute='first_name';

    public static function getGlobalSearchResultsTitle(Model $record): string{
        return $record->last_name;
    }

    public static function getGloballySearchableAttributes(): array{
        return ['first_name','last_name','country.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
{
    return [
        'Country ' => $record->country->name,
    ];
}

public static function getGlobalSearchEloquentQuery(): Builder
{
    return parent::getGlobalSearchEloquentQuery()->with([ 'country']);
}

public static function getNavigationBadge(): ?string
{
    return static::getModel()::count();
}

public static function getNavigationBadgeColor(): ?string{
        return static::getModel()::count() > 10 ? 'primary' : 'warning';
}


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               
                Forms\Components\Section::make('User Location')
                ->description('Put user location details in.')
                ->schema([
                
                    Forms\Components\Select::make('country_id')
                    ->relationship('country','name')
                    ->required()
                    ->live()
                    ->preload()
                   ->afterStateUpdated(function(callable $set) {
                        $set('state_id',null);
                        $set('city_id',null);
                   })
                    ->searchable(),
                    

                    Forms\Components\Select::make('state_id')
                    ->options(function (Get $get){
                        $country = Country::find($get('country_id'));
                        if(!$country){
                         return [];
                        }
                        return $country->states()->pluck('name','id');
                    })
                    ->required()
                    ->label('State')
                    ->afterStateUpdated(fn(callable $set) =>$set('city_id',null))
                    ->preload()
                    ->live()
                    ->searchable(),

                    Forms\Components\Select::make('city_id')
                    ->relationship('city','name')
                    ->required()
                    ->options(function (Get $get) {
                        $state = State::find($get('state_id'));
                        if(!$state){
                            return [];
                        }
                        return $state->cities()->pluck('name','id');
                    })
                    ->preload()
                    ->live()
                    ->searchable(),

                    Forms\Components\Select::make('department_id')
                    ->relationship('department','name')
                    ->required()
                    ->preload()
                    ->searchable(),

                ])->columns(2),
 

               Forms\Components\Section::make('User Name')
               ->description('Put user name details in.')
               ->schema([
                Forms\Components\TextInput::make('first_name')
                ->required(),
                Forms\Components\TextInput::make('last_name')
                ->required(),
                Forms\Components\TextInput::make('middle_name')
                ->required(),
               ])->columns(3),

               Forms\Components\Section::make('User Address')
               ->schema([ Forms\Components\TextInput::make('address')
               ->required(),
                Forms\Components\TextInput::make('zip_code')
               ->required(),
            ])->columns(2),
                
            Forms\Components\Section::make('Dates')
            ->schema([
                Forms\Components\DatePicker::make('date_of_birth')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),
            Forms\Components\DatePicker::make('date_hired')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y'),
            ])->columns(2),
           
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                 ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('city.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('department.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('first_name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                Tables\Columns\TextColumn::make('date_hired')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([


                // SelectFilter::make('zip_code')
                // ->options(
                //     Employee::distinct('zip_code')->pluck('zip_code','zip_code')
                // )
                SelectFilter::make('Country')
                ->relationship('country','name')
                ->preload()
                // ->searchable()
                ->multiple(),

                SelectFilter::make('Department')
                ->relationship('department','name')
                ->preload()
                // ->searchable()
                ->multiple(),

               

                Filter::make('created_at')
                ->form([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])
                // ...
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
            
                    if ($data['from'] ?? null) {
                        $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['from'])->toFormattedDateString())
                            ->removeField('from');
                    }
            
                    if ($data['until'] ?? null) {
                        $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['until'])->toFormattedDateString())
                            ->removeField('until');
                    }
            
                    return $indicators;
                })->columns(2)
            ],layout:FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->successNotificationTitle('Employee Deleted')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    

    public static function infolist(Infolist $infolist): Infolist
    {
       return $infolist
           ->schema([
              Section::make('Employee Info')
              ->schema([
              
                Section::make('Name Of Employee')
                ->schema([
                    TextEntry::make('first_name'),
                    TextEntry::make('middle_name'),
                    TextEntry::make('last_name'),
                ])->columns(3),

                Section::make('Location Of Employee')
                ->schema([
                    
               TextEntry::make('country.name'),
               TextEntry::make('state.name'),
               TextEntry::make('city.name'),
               
                ])->columns(3),

                Section::make('More Details')
                ->schema([
                TextEntry::make('department.name'),
                TextEntry::make('address'),
                TextEntry::make('zip_code'),
                ])->columns(3),
               

              Section::make()
              ->schema([
                TextEntry::make('date_of_birth'),
                TextEntry::make('date_hired'),
              ])->columns(3),


              ])
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
