@props([
    'label',
    'value',
    'icon',
    'color' => '#22C55E',
    'bg' => '#DCFCE7'
])

<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
         style="background-color: {{ $bg }}">
        <i data-lucide="{{ $icon }}" class="w-6 h-6" style="color: {{ $color }};"></i>
    </div>
    <div>
        <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
        <p class="text-sm text-gray-500 font-medium">{!! $label !!}</p>
    </div>
</div>
