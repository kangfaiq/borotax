<div class="password-standards-card">
    <div class="password-standards-title">Standar Password</div>
    <ul class="password-standards-list">
        @foreach(\App\Domain\Auth\Support\PasswordStandards::requirements() as $requirement)
            <li>{{ $requirement }}</li>
        @endforeach
    </ul>
</div>