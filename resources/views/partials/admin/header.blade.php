<?php
$auth = Auth::user();
$role = $auth->role;
?>

@if ($role == 'Super Admin')
    @include('partials.admin.header.admin')
@elseif ($role == 'Admin')
    @include('partials.admin.header.admin')
@elseif ($role == 'Editor')
    @include('partials.admin.header.editor')
@elseif ($role == 'Penulis')
    @include('partials.admin.header.penulis')
@elseif ($role == 'Kontributor')
    @include('partials.admin.header.kontributor')
@elseif ($role == 'Operator')
    @include('partials.admin.header.operator')
@endif
