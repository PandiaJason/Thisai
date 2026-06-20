<?php

namespace App\Filament\Faculty\Resources\LiveTelecasts;

use App\Models\LiveTelecast;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LiveTelecastResource extends Resource
{
    protected static ?string $model = LiveTelecast::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-play';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('created_by', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stream_url')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://iframe.mediadelivery.net/embed/...'),
                        Forms\Components\TextInput::make('thumbnail_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('scheduled_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->default('06:00:00'),
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->default('07:00:00'),
                        Forms\Components\DateTimePicker::make('auto_delete_at')
                            ->helperText('Defaults to 6:00 PM on the scheduled date.'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'scheduled' => 'Scheduled',
                                'live' => 'Live',
                                'ended' => 'Ended',
                                'deleted' => 'Deleted',
                            ])
                            ->default('scheduled'),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'live' => 'danger',
                        'ended' => 'warning',
                        'deleted' => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'live' => 'Live',
                        'ended' => 'Ended',
                        'deleted' => 'Deleted',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveTelecasts::route('/'),
            'create' => Pages\CreateLiveTelecast::route('/create'),
            'edit' => Pages\EditLiveTelecast::route('/{record}/edit'),
        ];
    }
}
