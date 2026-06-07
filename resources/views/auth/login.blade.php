@extends('layouts.app')

@section('title', 'Masuk Ke Sistem')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12 font-medium">
    <div class="max-w-md w-full bg-white border border-slate-200/60 rounded-3xl shadow-premium-md overflow-hidden">
        
        <!-- Header -->
        <div class="bg-indigo-650 text-white px-8 py-10 text-center relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-900 to-indigo-600 opacity-90"></div>
            <div class="relative z-10 space-y-2">
                <div class="mx-auto w-12 h-12 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mb-2">
                    <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                </div>
                <h2 class="text-2xl font-extrabold tracking-tight">Portal Masuk SIKD</h2>
                <p class="text-indigo-200 text-xs font-normal">Sistem Keamanan Desa Terintegrasi</p>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Alamat Email</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i data-lucide="mail" class="h-5 w-5 text-indigo-650"></i>
                        </div>
                        <input type="email" name="email" id="email" 
                            value="{{ request('email', old('email')) }}"
                            required 
                            placeholder="nama@email.com" 
                            class="block w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm placeholder-slate-450 focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800 @error('email') border-rose-500 @enderror">
                    </div>
                    @error('email')
                        <span class="text-xs font-semibold text-rose-600 block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi</label>
                    </div>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i data-lucide="lock" class="h-5 w-5 text-indigo-650"></i>
                        </div>
                        <input type="password" name="password" id="password" 
                            required 
                            value="password"
                            placeholder="••••••••" 
                            class="block w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm placeholder-slate-455 focus:border-indigo-550 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 transition-premium font-medium text-slate-800">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" 
                        class="h-4 w-4 rounded-sm border-slate-200 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-xs font-semibold text-slate-500">Ingat saya di perangkat ini</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-650 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-premium-sm flex items-center justify-center space-x-2 transition-premium hover:-translate-y-0.5">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk Aplikasi</span>
                </button>
            </form>

            <!-- Register Redirect -->
            <div class="mt-8 text-center text-xs text-slate-450 border-t border-slate-100 pt-6">
                <span>Belum punya akun warga?</span>
                <a href="{{ route('register') }}" class="font-bold text-indigo-650 hover:text-indigo-800 ml-1 transition-premium">Daftar Akun Baru</a>
            </div>
        </div>

    </div>
</div>
@endsection
