@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/60 shadow-premium-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna Aplikasi</h1>
            <p class="text-sm text-slate-500 mt-1 font-normal">Daftarkan akun warga, satpam, perangkat, atau kades dan sesuaikan hak akses mereka.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Form Tambah Pengguna -->
        <div class="lg:col-span-4 bg-white border border-slate-200/60 rounded-3xl p-6 shadow-premium-sm space-y-5 font-medium">
            <h3 class="font-extrabold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center space-x-1.5">
                <i data-lucide="user-plus" class="w-5 h-5 text-indigo-650"></i>
                <span>Tambah Pengguna Baru</span>
            </h3>

            <form action="{{ route('perangkat.users.store') }}" method="POST" class="space-y-3.5">
                @csrf

                <div class="space-y-1">
                    <label for="name" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</label>
                    <input type="text" name="name" id="name" required placeholder="Hendra Wijaya"
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800">
                </div>

                <div class="space-y-1">
                    <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Email</label>
                    <input type="email" name="email" id="email" required placeholder="hendra@desa.id"
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi</label>
                        <input type="password" name="password" id="password" required placeholder="Min. 8 char"
                            class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800">
                    </div>

                    <div class="space-y-1">
                        <label for="role_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hak Akses / Peran</label>
                        <select name="role_id" id="role_id" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 bg-white font-medium text-slate-800">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="phone" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nomor Telepon/HP</label>
                    <input type="text" name="phone" id="phone" placeholder="08XXXXXXXXXX"
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800">
                </div>

                <div class="space-y-1">
                    <label for="address" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Rumah (RT/RW)</label>
                    <input type="text" name="address" id="address" placeholder="RT 03 / RW 02, Dusun Mulyo"
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium text-slate-800">
                </div>

                <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5 mt-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </form>
        </div>

        <!-- Right: Daftar Pengguna -->
        <div class="lg:col-span-8 bg-white border border-slate-200/60 rounded-3xl shadow-premium-sm overflow-hidden font-medium">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h2 class="font-extrabold text-slate-900 text-base">Daftar Anggota / Warga Terdaftar</h2>
                <span class="text-xs text-slate-450 font-bold">Seluruh pengguna sistem</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">Nama Lengkap</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4">Hak Akses / Peran</th>
                            <th class="px-6 py-4">Alamat Rumah</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-xs">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-50/25 transition text-slate-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-slate-900 block font-bold text-sm">{{ $user->name }}</span>
                                    <span class="text-slate-450 block mt-0.5 font-normal">{{ $user->email }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700">
                                    {{ $user->phone ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role->name === 'kades')
                                        <span class="px-2 py-0.5 rounded-sm font-bold bg-amber-50 text-amber-800 border border-amber-100 uppercase tracking-wide text-[9px]">Kades</span>
                                    @elseif($user->role->name === 'perangkat')
                                        <span class="px-2 py-0.5 rounded-sm font-bold bg-indigo-50 text-indigo-800 border border-indigo-100 uppercase tracking-wide text-[9px]">Perangkat</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-sm font-bold bg-slate-50 text-slate-800 border border-slate-200/60 uppercase tracking-wide text-[9px]">Warga</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-normal text-slate-600">
                                    {{ $user->address ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                    <button type="button" 
                                        onclick="openEditUserModal({{ json_encode($user) }})"
                                        class="inline-flex items-center p-1.5 bg-slate-105 hover:bg-slate-200 text-slate-700 rounded-xl transition-premium hover:-translate-y-0.5">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('perangkat.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Seluruh data terkait (laporan/jadwal) juga akan terpengaruh.')" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center p-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl transition-premium hover:-translate-y-0.5 border border-rose-100">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>

</div>

<!-- Modal Edit User -->
<div id="editUserModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white border border-slate-200/60 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden font-medium">
        <div class="px-6 py-4 border-b border-slate-150 flex justify-between items-center bg-slate-50">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center space-x-2">
                <i data-lucide="user" class="w-5 h-5 text-indigo-650"></i>
                <span>Edit Data Pengguna</span>
            </h3>
            <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 transition focus:outline-hidden">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <form id="editUserForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            <div class="space-y-1.5">
                <label for="edit_name" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required
                    class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 font-medium text-slate-800">
            </div>

            <div class="space-y-1.5">
                <label for="edit_email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Email</label>
                <input type="email" name="email" id="edit_email" required
                    class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 font-medium text-slate-800">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit_password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi Baru (Opsional)</label>
                    <input type="password" name="password" id="edit_password" placeholder="Kosongkan jika tetap"
                        class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 font-medium text-slate-800">
                </div>

                <div class="space-y-1.5">
                    <label for="edit_role_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hak Akses / Peran</label>
                    <select name="role_id" id="edit_role_id" required class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 bg-white font-medium text-slate-800">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="edit_phone" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nomor Telepon/HP</label>
                <input type="text" name="phone" id="edit_phone"
                    class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 font-medium text-slate-800">
            </div>

            <div class="space-y-1.5">
                <label for="edit_address" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Rumah (RT/RW)</label>
                <input type="text" name="address" id="edit_address"
                    class="block w-full rounded-xl border border-slate-200 py-2.5 px-3 text-sm focus:border-indigo-550 font-medium text-slate-800">
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-500 hover:bg-slate-50 transition-premium">Batal</button>
                <button type="submit" class="bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-premium-sm transition-premium hover:-translate-y-0.5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        const modal = document.getElementById('editUserModal');
        const form = document.getElementById('editUserForm');
        
        // Set Action URL
        form.action = `/perangkat/pengguna/${user.id}/update`;
        
        // Populate fields
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role_id').value = user.role_id;
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_address').value = user.address || '';
        document.getElementById('edit_password').value = ''; // Reset password field
        
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }
</script>
@endsection
