<div class="space-y-4 p-4">
    {{-- Header Info --}}
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="font-semibold text-gray-500">Action:</span>
            <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                {{ $record->action === 'created' ? 'bg-green-100 text-green-800' : '' }}
                {{ $record->action === 'updated' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $record->action === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
            ">
                {{ ucfirst($record->action) }}
            </span>
        </div>
        <div>
            <span class="font-semibold text-gray-500">Model:</span>
            <span class="ml-1">{{ class_basename($record->model_type) }} #{{ $record->model_id }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-500">User:</span>
            <span class="ml-1">{{ $record->user_name ?? 'System' }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-500">Date:</span>
            <span class="ml-1">{{ $record->created_at->format('M j, Y g:i:s A') }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-500">IP Address:</span>
            <span class="ml-1">{{ $record->ip_address ?? '—' }}</span>
        </div>
        <div>
            <span class="font-semibold text-gray-500">Record:</span>
            <span class="ml-1">{{ $record->model_label }}</span>
        </div>
    </div>

    {{-- Changes Table --}}
    @if($record->action === 'updated' && $record->old_values && $record->new_values)
        <div class="mt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Changes</h4>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Field</th>
                        <th class="px-3 py-2 text-left font-medium text-red-600">Old Value</th>
                        <th class="px-3 py-2 text-left font-medium text-green-600">New Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($record->new_values as $field => $newVal)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-700">{{ $field }}</td>
                            <td class="px-3 py-2 text-red-700 bg-red-50">
                                {{ is_array($record->old_values[$field] ?? null) ? json_encode($record->old_values[$field]) : ($record->old_values[$field] ?? '—') }}
                            </td>
                            <td class="px-3 py-2 text-green-700 bg-green-50">
                                {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Created Values --}}
    @if($record->action === 'created' && $record->new_values)
        <div class="mt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Created Values</h4>
            <div class="bg-green-50 rounded-lg p-3 text-sm space-y-1">
                @foreach($record->new_values as $field => $value)
                    <div>
                        <span class="font-medium text-gray-600">{{ $field }}:</span>
                        <span class="text-green-800 ml-1">{{ is_array($value) ? json_encode($value) : $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Deleted Values --}}
    @if($record->action === 'deleted' && $record->old_values)
        <div class="mt-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Deleted Values</h4>
            <div class="bg-red-50 rounded-lg p-3 text-sm space-y-1">
                @foreach($record->old_values as $field => $value)
                    <div>
                        <span class="font-medium text-gray-600">{{ $field }}:</span>
                        <span class="text-red-800 ml-1">{{ is_array($value) ? json_encode($value) : $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
