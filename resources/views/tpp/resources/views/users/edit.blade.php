@extends('layouts.main')

@section('title','Edit User')

@section('content')

<h3 class="mb-3">Edit User</h3>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('users.update', $user->id) }}">
@csrf
@method('PUT')

<div class="mb-3">
    <label>Nama</label>
    <input type="text" name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
</div>

<div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required>
</div>

<div class="mb-3">
    <label>Role</label>
    <select name="role" class="form-control" required>
        <option value="admin" {{ old('role',$user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="operator" {{ old('role',$user->role) == 'operator' ? 'selected' : '' }}>Operator</option>
        <option value="viewer" {{ old('role',$user->role) == 'viewer' ? 'selected' : '' }}>Viewer</option>
    </select>
</div>

<hr>

<h5>Reset Password (Opsional)</h5>
<p class="text-muted">Kosongkan jika tidak ingin mengubah password.</p>

<div class="mb-3">
    <label>Password Baru</label>
    <input type="password" name="password" class="form-control">
</div>

<div class="mb-3">
    <label>Konfirmasi Password Baru</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>

<button class="btn btn-primary">Update</button>
<a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>

</form>

@endsection