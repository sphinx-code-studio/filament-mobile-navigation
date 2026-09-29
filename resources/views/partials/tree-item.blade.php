@php
    use function Filament\Support\generate_href_html;
    use function Filament\Support\generate_icon_html;
    use Filament\Support\Enums\IconSize;
@endphp

<li class="fmn-tree-entry">
    @if ($item->url)
        <a
            {{ generate_href_html($item->url, $item->openInNewTab) }}
            @class(['fmn-tree-item', 'fmn-active' => $item->active])
            style="--fmn-depth: {{ $depth }}"
            @if ($item->active) aria-current="page" @endif
        >
            <span class="fmn-tree-icon">
                {{ generate_icon_html($item->icon, size: IconSize::Medium) }}
            </span>
            <span class="fmn-tree-label">{{ $item->label }}</span>
            @if (filled($item->badge))
                <x-filament::badge :color="$item->badgeColor ?? 'primary'" size="sm">
                    {{ $item->badge }}
                </x-filament::badge>
            @endif
        </a>
    @else
        <div class="fmn-tree-item fmn-tree-heading" style="--fmn-depth: {{ $depth }}">
            <span class="fmn-tree-icon">
                {{ generate_icon_html($item->icon, size: IconSize::Medium) }}
            </span>
            <span class="fmn-tree-label">{{ $item->label }}</span>
        </div>
    @endif

    @if ($item->children !== [])
        <ul class="fmn-tree fmn-tree-children" role="list">
            @foreach ($item->children as $child)
                @include('filament-mobile-navigation::partials.tree-item', ['item' => $child, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
