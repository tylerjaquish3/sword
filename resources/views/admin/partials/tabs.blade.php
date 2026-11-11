<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $active === 'users' ? 'active' : '' }}" href="{{ route('admin.users') }}">User Management
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $active === 'formatting' ? 'active' : '' }}" href="{{ route('admin.formatting') }}">Formatting
        </a>
    </li>
</ul>
