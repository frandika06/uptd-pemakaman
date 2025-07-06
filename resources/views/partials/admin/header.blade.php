<?php
$auth = Auth::user();
$role = $auth->role;
?>

@if (Request::is('backend/cms*'))
    @if ($role == 'Super Admin' || $role == 'Admin' || $role == 'Editor')
        @include('partials.admin.header.cms.admin')
    @elseif ($role == 'Penulis' || $role == 'Kontributor')
        @include('partials.admin.header.cms.penulis')
    @elseif ($role == 'Operator')
        @include('partials.admin.header.cms.operator')
    @endif
@elseif(Request::is('backend/tpu*'))
    @if ($role == 'Super Admin')
        @include('partials.admin.header.tpu.admin')
    @elseif ($role == 'Admin')
        @include('partials.admin.header.tpu.admin')
    @elseif ($role == 'Editor')
        @include('partials.admin.header.tpu.editor')
    @elseif ($role == 'Penulis')
        @include('partials.admin.header.tpu.penulis')
    @elseif ($role == 'Kontributor')
        @include('partials.admin.header.tpu.kontributor')
    @elseif ($role == 'Operator')
        @include('partials.admin.header.tpu.operator')
    @endif
@elseif(Request::is('backend/helpdesk*'))
    @include('partials.admin.header.helpdesk.admin')
@elseif(Request::is('backend/pengaturan*'))
    @if ($role == 'Super Admin' || $role == 'Admin')
        @include('partials.admin.header.setup.admin')
    @else
        @include('partials.admin.header.setup.user')
    @endif
@else
    @include('partials.admin.header.default')
@endif
