@extends('layouts.app')

@section('title', 'Daftar Akun Warga')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12 font-medium">
    <div class="max-w-lg w-full bg-white border border-slate-200/60 rounded-3xl shadow-premium-md overflow-hidden">
        
        <!-- Header -->
        <div class="bg-indigo-650 text-white px-8 py-10 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-indigo-700 opacity-95"></div>
            <div class="relative z-10 space-y-2">
                <div class="mx-auto w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-2 border border-white/15">
                    <i data-lucide="user-plus" class="w-6 h-6 text-amber-400"></i>
                </div>
                <h2 class="text-xl font-extrabold tracking-tight uppercase">Registrasi Akun Warga</h2>
                <p class="text-slate-355 text-xs font-semibold">Pemerintah Desa Awa, Kec. Samaturu</p>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="space-y-1">
                        <label for="name" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="Budi Santoso" 
                            class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800 @error('name') border-rose-500 @enderror">
                        @error('name')
                            <span class="text-xs font-semibold text-rose-600 block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-1">
                        <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Email</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="budi@email.com" 
                            class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800 @error('email') border-rose-500 @enderror">
                        @error('email')
                            <span class="text-xs font-semibold text-rose-600 block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- No Telepon/HP -->
                    <div class="space-y-1">
                        <label for="phone" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nomor Telepon / HP</label>
                        <input type="text" name="phone" id="phone" required value="{{ old('phone') }}" placeholder="0812XXXXXXXX" 
                            class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800 @error('phone') border-rose-500 @enderror">
                        @error('phone')
                            <span class="text-xs font-semibold text-rose-600 block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Alamat / RT / RW -->
                    <div class="space-y-1">
                        <label for="address" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Lengkap (RT/RW)</label>
                        <input type="text" name="address" id="address" required value="{{ old('address') }}" placeholder="RT 01 / RW 01, Dusun Krajan" 
                            class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800 @error('address') border-rose-500 @enderror">
                        @error('address')
                            <span class="text-xs font-semibold text-rose-600 block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Password -->
                    <div class="space-y-1">
                        <label for="password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi</label>
                        <input type="password" name="password" id="password" required placeholder="Min. 8 Karakter" 
                            class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800 @error('password') border-rose-500 @enderror">
                        @error('password')
                            <span class="text-xs font-semibold text-rose-600 block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-1">
                        <label for="password_confirmation" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Ulangi Kata Sandi</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Ulangi Sandi" 
                            class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5 mt-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Daftar Sekarang</span>
                </button>
            </form>

            <!-- Login Redirect -->
            <div class="mt-6 text-center text-xs text-slate-450 border-t border-slate-100 pt-6">
                <span>Sudah memiliki akun?</span>
                <a href="{{ route('login') }}" class="font-bold text-indigo-650 hover:text-indigo-800 ml-1 transition-premium">Masuk di sini</a>
            </div>
        </div>

    </div>
</div>
@endsection
