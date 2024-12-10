<nav class="navbar navbar-expand navbar-light bg-light print-header-content">
    <ul class="navbar-nav me-auto">
        <li class="nav-item active">
            <a id="sidebar-toggle" class="sidebar-toggle nav-link" href="#">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item">
        </li>

        <li class="nav-item">
        </li>
    </ul>

{{--    @include('layouts.partial.language_switcher')--}}

    <div class="dropdown text-end">
        <a  href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
{{--            <img src="https://github.com/mdo.png" alt="mdo" class="rounded-circle" width="32" height="32">--}}
            {{auth()->user()?auth()->user()->name:''}}
        </a>
        <ul class="dropdown-menu text-small" aria-labelledby="dropdownUser1" style="">
            <li><a class="dropdown-item" href="#">Profile</a></li>
            @if(auth()->user()->is_admin === 1)
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('user_reset_password',[app()->getLocale()]) }}">  Reset Password</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    {{ __('Sign out') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</nav>
