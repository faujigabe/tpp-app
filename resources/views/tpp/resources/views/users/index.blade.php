@extends('layouts.main')

@section('title','Manajemen User')

@section('content')

<h3 class="mb-3">Manajemen User</h3>

<a href="{{ route('users.create') }}" class="btn btn-primary mb-3">
    Tambah User
</a>

<table class="table table-bordered">
<thead>
<tr>
<th>Nama</th>
<th>Email</th>
<th>Role</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>
@foreach($users as $user)
<tr>
<td>{{ $user->name }}</td>
<td>{{ $user->email }}</td>
<td>{{ ucfirst($user->role) }}</td>

<td class="d-flex gap-2">

    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
        Edit
    </a>

    @if(auth()->id() != $user->id)
    <form method="POST" action="{{ route('users.destroy',$user->id) }}">
    @csrf
    @method('DELETE')

    <button onclick="return confirm('Hapus user?')" class="btn btn-danger btn-sm">
    Hapus
    </button>

    </form>
    @endif

    </td>

</tr>
@endforeach
</tbody>

</table>

@endsection