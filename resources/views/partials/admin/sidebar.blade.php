<?php
$auth = Auth::user();
$role = $auth->role;
?>

@if ($role == 'Super Admin')
    @include('partials.admin.sidebar.admin')
@elseif ($role == 'Admin')
    @include('partials.admin.sidebar.admin')
@elseif ($role == 'Editor')
    @include('partials.admin.sidebar.editor')
@elseif ($role == 'Penulis')
    @include('partials.admin.sidebar.penulis')
@elseif ($role == 'Kontributor')
    @include('partials.admin.sidebar.kontributor')
@elseif ($role == 'Operator')
    @include('partials.admin.sidebar.operator')
@endif
