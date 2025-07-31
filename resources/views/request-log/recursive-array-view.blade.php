@foreach ($data as $key => $value)
    @if (is_array($value))
        <li>
            <strong style="font-weight: bold">{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
            <ul class="list-unstyled ms-3">
                @include('request-log/recursive-array-view', ['data' => $value])
            </ul>
        </li>
    @else
        <li>
            <strong  style="font-weight: bold">{{ $key }}:</strong>
            {{ $value }}
        </li>
    @endif
@endforeach
