<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CustomizationRelationManager extends RelationManager
{
    protected static string $relationship = 'customization';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Repeater::make('customizations')
                    ->label('Customization Groups')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('group_name')
                            ->label('Group Name')
                            ->required()
                            ->placeholder('e.g. size, sweetness, milk, addons')
                            ->helperText('Key name used in the API (lowercase, no spaces)'),
                        \Filament\Forms\Components\Toggle::make('isMultiSelect')
                            ->label('Allow multiple selections?')
                            ->default(false)
                            ->helperText('Enable for addons/toppings'),
                        \Filament\Forms\Components\Repeater::make('options')
                            ->label('Options')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->placeholder('e.g. Regular, Large, Oat'),
                                \Filament\Forms\Components\TextInput::make('price')
                                    ->label('Extra Price')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(0),
                                \Filament\Forms\Components\TextInput::make('label')
                                    ->label('Display Label (Optional)')
                                    ->placeholder('e.g. Full Sweet'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['group_name'] ?? null)
                    // Load: convert keyed object → repeater array
                    ->afterStateHydrated(function ($component, $state) {
                        if (empty($state) || !is_array($state)) {
                            $component->state([]);
                            return;
                        }

                        // Already list format (from previous save)
                        if (array_is_list($state)) {
                            return;
                        }

                        // Convert keyed object { "size": {...} } to array for repeater
                        $converted = [];
                        foreach ($state as $groupName => $groupData) {
                            if (!is_array($groupData)) continue;

                            $options = [];
                            if (isset($groupData['options']) && is_array($groupData['options'])) {
                                foreach ($groupData['options'] as $opt) {
                                    if (is_array($opt)) {
                                        $options[] = [
                                            'name' => $opt['name'] ?? '',
                                            'price' => $opt['price'] ?? 0,
                                            'label' => $opt['label'] ?? null,
                                        ];
                                    }
                                }
                            }

                            $converted[] = [
                                'group_name' => $groupName,
                                'isMultiSelect' => $groupData['isMultiSelect'] ?? false,
                                'options' => $options,
                            ];
                        }

                        $component->state($converted);
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('groups_display')
                    ->label('Groups')
                    ->getStateUsing(function ($record) {
                        $state = $record->customizations;
                        if (empty($state) || !is_array($state)) {
                            return 'None';
                        }

                        $groups = [];
                        foreach ($state as $key => $value) {
                            // Determine group name and data
                            $groupName = is_numeric($key) 
                                ? ($value['group_name'] ?? null) 
                                : $key;
                            
                            // Skip items without a valid group name (bad data)
                            if (!$groupName || !is_array($value) || $groupName === 'options' || $groupName === 'isMultiSelect') continue;

                            $options = $value['options'] ?? [];
                            if (empty($options) || !is_array($options)) {
                                $groups[] = ucfirst($groupName) . ': (no options)';
                                continue;
                            }

                            // Format options: "name - P{price}"
                            $optionLabels = [];
                            foreach ($options as $opt) {
                                if (!is_array($opt) || empty($opt['name'])) continue;
                                $price = number_format((float)($opt['price'] ?? 0), 2);
                                $optionLabels[] = $opt['name'] . ' - P' . $price;
                            }

                            $groups[] = ucfirst($groupName) . ': ' . implode(', ', $optionLabels);
                        }

                        return !empty($groups) ? implode(' | ', $groups) : 'None';
                    })
                    ->wrap(),
            ])
            ->filters([])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['customizations'] = self::transformCustomizationsForSave($data['customizations'] ?? []);
                        return $data;
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['customizations'] = self::transformCustomizationsForSave($data['customizations'] ?? []);
                        return $data;
                    }),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Transform repeater array format to keyed object format for database storage.
     * 
     * Input (from Filament Repeater):
     * [
     *   { "group_name": "size", "isMultiSelect": false, "options": [{ "name": "Regular", "price": 0 }] },
     *   { "group_name": "addons", "isMultiSelect": true, "options": [...] }
     * ]
     * 
     * Output (for database):
     * {
     *   "size": { "isMultiSelect": false, "options": [{ "name": "Regular", "price": 0 }] },
     *   "addons": { "isMultiSelect": true, "options": [...] }
     * }
     */
    public static function transformCustomizationsForSave(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            // Skip items without group_name
            $groupName = $item['group_name'] ?? null;
            if (!$groupName) continue;

            // Clean options array — strip UUID keys, build clean sequential array
            $cleanOptions = [];
            $options = $item['options'] ?? [];
            foreach ($options as $opt) {
                if (!is_array($opt) || empty($opt['name'])) continue;
                $option = [
                    'name' => $opt['name'],
                    'price' => (int)($opt['price'] ?? 0),
                ];
                if (!empty($opt['label'])) {
                    $option['label'] = $opt['label'];
                }
                $cleanOptions[] = $option;
            }

            $result[$groupName] = [
                'isMultiSelect' => (bool)($item['isMultiSelect'] ?? false),
                'options' => $cleanOptions,
            ];
        }

        return $result;
    }
}
