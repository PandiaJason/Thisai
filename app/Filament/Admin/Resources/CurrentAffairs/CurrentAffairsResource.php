<?php

namespace App\Filament\Admin\Resources\CurrentAffairs;

use App\Models\CurrentAffairs;
use App\Models\User;
use App\Enums\CurrentAffairsType;
use App\Enums\UserRole;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CurrentAffairsResource extends Resource
{
    protected static ?string $model = CurrentAffairs::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->unique(CurrentAffairs::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name'),
                        Forms\Components\Select::make('author_id')
                            ->relationship('author', 'name', fn ($query) => $query->whereIn('role', [UserRole::FACULTY->value, UserRole::SUPER_ADMIN->value]))
                            ->default(fn () => auth()->id())
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(array_combine(
                                array_map(fn($type) => $type->value, CurrentAffairsType::cases()),
                                array_map(fn($type) => $type->label(), CurrentAffairsType::cases())
                            ))
                            ->default(CurrentAffairsType::DAILY->value),
                        Forms\Components\DatePicker::make('publish_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\TagsInput::make('tags'),
                        Forms\Components\Toggle::make('is_published')
                            ->required()
                            ->default(false),
                        Forms\Components\RichEditor::make('content')
                            ->required()
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
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (CurrentAffairsType $state): string => match ($state) {
                        CurrentAffairsType::DAILY => 'success',
                        CurrentAffairsType::EDITORIAL => 'warning',
                        CurrentAffairsType::PIB => 'info',
                        CurrentAffairsType::SCHEME => 'primary',
                        CurrentAffairsType::FACT => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('publish_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(
                        array_map(fn($type) => $type->value, CurrentAffairsType::cases()),
                        array_map(fn($type) => $type->label(), CurrentAffairsType::cases())
                    )),
                Tables\Filters\TernaryFilter::make('is_published'),
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
            'index' => Pages\ListCurrentAffairs::route('/'),
            'create' => Pages\CreateCurrentAffairs::route('/create'),
            'edit' => Pages\EditCurrentAffairs::route('/{record}/edit'),
        ];
    }
}
