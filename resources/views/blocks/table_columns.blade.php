@use('Illuminate\Support\Facades\Schema')
@use('Illuminate\Support\Str')
@use('Mainstay\Facades\Mainstay')
@php
    /* Each field's column as the database has it now, in the type's table or
       the one beside it holding what is translated. A field kept in rows of
       its own, as tags are, has none. */
    $type = Mainstay::registered()[$block->of] ?? null;
    $rows = [];

    if ($type !== null) {
        $tables = [$block->of => Schema::getColumns($block->of), $block->of.'_locales' => Schema::getColumns($block->of.'_locales')];

        foreach (Mainstay::fields($type) as $field) {
            $table = $field->localized ? $block->of.'_locales' : $block->of;
            $column = collect($tables[$table])->firstWhere('name', Str::snake($field->name));

            if ($column !== null) {
                $rows[] = ['table' => $table, 'column' => $column['name'], 'type' => $column['type'].($column['nullable'] ? ', null' : '')];
            }
        }
    }
@endphp
<div class="table-scroll">
    <table class="tbl">
        <thead><tr><th scope="col">{{ __('Table') }}</th><th scope="col">{{ __('Column') }}</th><th scope="col">{{ __('Type') }}</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr><td class="muted">{{ $row['table'] }}</td><td>{{ $row['column'] }}</td><td class="muted">{{ $row['type'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div>
