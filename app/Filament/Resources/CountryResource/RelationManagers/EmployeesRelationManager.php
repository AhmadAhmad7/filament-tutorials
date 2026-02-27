<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Models\Country;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public  function form(Form $form): Form
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
                    ->options(function (callable $get){
                        $country = Country::find($get('country_id'));
                        if(!$country){
                         return [];
                        }
                        return $country->states()->pluck('name','id');
                    })
                    ->required()
                    ->label('State')
                    // ->afterStateUpdated(fn(Set $set) =>  $set('city_id',null) )
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
