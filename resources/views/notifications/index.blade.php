@extends('layouts.main')

@section('title', 'Notifikasi')

@section('content')
@php
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h3 class="mb-1">Pusat Notifikasi</h3>
        <p class="text-muted mb-0">Pantau pengajuan, validasi, dan pembukaan kembali periode TPP yang memerlukan perhatian Anda.</p>
    </div>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-primary"><i class="bi bi-check2-all me-2"></i>Tandai Semua Dibaca</button>
    </form>
    @endif
</div>

<div class="app-card p-3 mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('notifications.index') }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-light border' }}">Semua</a>
        <a href="{{ route('notifications.index', ['status' => 'unread']) }}" class="btn btn-sm {{ $status === 'unread' ? 'btn-primary' : 'btn-light border' }}">Belum Dibaca <span class="badge text-bg-danger ms-1">{{ $unreadCount }}</span></a>
        <a href="{{ route('notifications.index', ['status' => 'read']) }}" class="btn btn-sm {{ $status === 'read' ? 'btn-primary' : 'btn-light border' }}">Sudah Dibaca</a>
    </div>
</div>

<div class="app-card overflow-hidden">
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            @php
                $data = is_array($notification->data) ? $notification->data : [];
                $isUnread = is_null($notification->read_at);
                $notificationStatus = $data['status'] ?? 'draft';
                $iconClass = match ($notificationStatus) {
                    'submitted' => 'bi-send-check text-warning',
                    'locked' => 'bi-lock-fill text-success',
                    default => 'bi-unlock-fill text-primary',
                };
                $periode = isset($data['bulan'], $data['tahun'])
                    ? (($bulanNama[(int) $data['bulan']] ?? $data['bulan']) . ' ' . $data['tahun'])
                    : null;
            @endphp
            <div class="list-group-item p-4 {{ $isUnread ? 'bg-primary bg-opacity-10' : '' }}">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-md-start">
                    <div class="fs-4"><i class="bi {{ $iconClass }}"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <div class="fw-bold">{{ $data['title'] ?? 'Pembaruan TPP' }}</div>
                            @if($isUnread)<span class="badge text-bg-primary">Baru</span>@endif
                        </div>
                        <p class="mb-2 text-muted">{{ $data['message'] ?? 'Terdapat pembaruan pada periode TPP.' }}</p>
                        <div class="small text-muted d-flex flex-wrap gap-3">
                            @if(!empty($data['unit_kerja_name']))<span><i class="bi bi-building me-1"></i>{{ $data['unit_kerja_name'] }}</span>@endif
                            @if($periode)<span><i class="bi bi-calendar3 me-1"></i>{{ $periode }}</span>@endif
                            @if(!empty($data['actor_name']))<span><i class="bi bi-person me-1"></i>{{ $data['actor_name'] }}</span>@endif
                            <span><i class="bi bi-clock me-1"></i>{{ $notification->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button class="btn btn-sm {{ $isUnread ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $isUnread ? 'Baca & Buka' : 'Buka Periode' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-5 text-center">
                <i class="bi bi-bell-slash fs-1 text-muted"></i>
                <h5 class="mt-3">Belum ada notifikasi</h5>
                <p class="text-muted mb-0">Pembaruan alur TPP akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="p-3 border-top">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
