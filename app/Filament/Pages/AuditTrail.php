<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class AuditTrail extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?int $navigationSort = 99;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationLabel(): string
    {
        return 'Audit Trail';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Audit Trail';
    }

    public function getView(): string
    {
        return 'filament.pages.audit-trail';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('model_name')
                    ->label('Model')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('model_type', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('model_label')
                    ->label('Record')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('changes_summary')
                    ->label('Changes')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
                Tables\Filters\SelectFilter::make('model_type')
                    ->label('Model')
                    ->options(function () {
                        return AuditLog::query()
                            ->distinct('model_type')
                            ->pluck('model_type', 'model_type')
                            ->mapWithKeys(fn ($v, $k) => [$k => class_basename($k)])
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn (AuditLog $record) => "{$record->action} — {$record->model_label}")
                    ->modalContent(fn (AuditLog $record) => view('filament.pages.audit-detail', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
