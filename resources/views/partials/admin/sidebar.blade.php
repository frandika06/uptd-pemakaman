<?php
$auth = Auth::user();
$role = $auth->role;
?>

@if ($role == 'Super Admin' || $role == 'Admin' || $role == 'Super Editor')
    @include('partials.admin.sidebar.admin')
@elseif ($role == 'Penulis' || $role == 'Kontributor')
    @include('partials.admin.sidebar.penulis')
@elseif ($role == 'Operator')
    @include('partials.admin.sidebar.operator')
@endif
