@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'p-12 text-center text-slate-400']) }}>
    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 border border-slate-100">
        <i data-lucide="{{ $icon }}" class="w-7 h-7 text-slate-300"></i>
    </div>
    <h4 class="font-extrabold text-slate-700 text-sm">{{ $title }}</h4>
    @if($description)
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto font-normal">{{ $description }}</p>
    @endif
</div>
