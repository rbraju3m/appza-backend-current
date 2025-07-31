<div class="dropdown text-end">

    <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
        @php
            $key = array_search ($current_locale, $available_locales);
        @endphp
        {{$key}}
    </a>
    <ul class="dropdown-menu text-small" aria-labelledby="dropdownUser1" style="">
        @foreach($available_locales as $locale_name => $available_locale)
            <li><a class="dropdown-item" href="{{route('language_switcher',$available_locale)}}">{{ $locale_name }}</a></li>
        @endforeach
    </ul>
</div>
