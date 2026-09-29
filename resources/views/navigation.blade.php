@php
    use SphinxCodeStudio\FilamentMobileNavigation\Navigation\ResolvedNavigationItem;
    use function Filament\Support\generate_href_html;
    use function Filament\Support\generate_icon_html;
    use Filament\Support\Enums\IconSize;
@endphp

<div
    x-data="{
        open: false,
        openMenu() {
            this.open = true
            this.$nextTick(() => this.$refs.closeButton?.focus())
        },
        closeMenu() {
            this.open = false
            this.$nextTick(() => this.$refs.menuButton?.focus())
        },
    }"
    x-on:keydown.escape.window="closeMenu()"
    @class([
        'fmn-root',
        'fmn-hides-original-trigger' => $hideOriginalMobileNavigationTrigger,
    ])
>
    <nav class="fmn-bar" aria-label="{{ __('filament-mobile-navigation::navigation.label') }}">
        @foreach ($entries as $entry)
            @if ($entry === 'menu')
                <button
                    x-ref="menuButton"
                    type="button"
                    @class(['fmn-bar-item fmn-menu-trigger', 'fmn-active' => $moreActive])
                    x-on:click="openMenu()"
                    x-bind:aria-expanded="open.toString()"
                    aria-controls="fmn-more-menu"
                    aria-label="{{ $moreActive ? __('filament-mobile-navigation::navigation.more_current') : __('filament-mobile-navigation::navigation.more') }}"
                >
                    <span class="fmn-icon-wrap">
                        {{ generate_icon_html('heroicon-o-ellipsis-horizontal-circle', size: IconSize::Large) }}
                    </span>
                    <span class="fmn-label">{{ __('filament-mobile-navigation::navigation.more') }}</span>
                </button>
            @else
                @php
                    /** @var ResolvedNavigationItem $entry */
                @endphp
                <a
                    {{ generate_href_html($entry->url, $entry->openInNewTab) }}
                    @class(['fmn-bar-item', 'fmn-active' => $entry->active])
                    @if ($entry->active) aria-current="page" @endif
                    aria-label="{{ $entry->label }}"
                >
                    <span class="fmn-icon-wrap">
                        {{ generate_icon_html($entry->icon, size: IconSize::Large) }}
                        @if (filled($entry->badge))
                            <x-filament::badge
                                :color="$entry->badgeColor ?? 'primary'"
                                size="xs"
                                class="fmn-bar-badge"
                            >
                                {{ $entry->badge }}
                            </x-filament::badge>
                        @endif
                    </span>
                    <span class="fmn-label">{{ $entry->label }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    @if ($hasMore)
        <div
            x-cloak
            x-show="open"
            x-trap.inert.noscroll="open"
            class="fmn-sheet-layer"
            role="presentation"
        >
            <button
                type="button"
                class="fmn-backdrop"
                x-on:click="closeMenu()"
                aria-label="{{ __('filament-mobile-navigation::navigation.close') }}"
                tabindex="-1"
            ></button>

            <section
                id="fmn-more-menu"
                class="fmn-sheet"
                role="dialog"
                aria-modal="true"
                aria-labelledby="fmn-more-title"
                x-transition:enter="fmn-sheet-enter"
                x-transition:enter-start="fmn-sheet-enter-start"
                x-transition:enter-end="fmn-sheet-enter-end"
                x-transition:leave="fmn-sheet-leave"
                x-transition:leave-start="fmn-sheet-leave-start"
                x-transition:leave-end="fmn-sheet-leave-end"
            >
                <header class="fmn-sheet-header">
                    <h2 id="fmn-more-title" class="fmn-sheet-title">
                        {{ __('filament-mobile-navigation::navigation.more') }}
                    </h2>
                    <button
                        x-ref="closeButton"
                        type="button"
                        class="fmn-close"
                        x-on:click="closeMenu()"
                        aria-label="{{ __('filament-mobile-navigation::navigation.close') }}"
                    >
                        {{ generate_icon_html('heroicon-o-x-mark', size: IconSize::Medium) }}
                    </button>
                </header>

                <div class="fmn-sheet-content">
                    @foreach ($groups as $group)
                        <section class="fmn-group" @if ($group->label) aria-labelledby="fmn-group-{{ $loop->index }}" @endif>
                            @if ($group->label)
                                <h3 id="fmn-group-{{ $loop->index }}" class="fmn-group-label">
                                    @if ($group->icon)
                                        {{ generate_icon_html($group->icon, size: IconSize::Small) }}
                                    @endif
                                    <span>{{ $group->label }}</span>
                                </h3>
                            @endif

                            <ul class="fmn-tree" role="list">
                                @foreach ($group->items as $item)
                                    @include('filament-mobile-navigation::partials.tree-item', ['item' => $item, 'depth' => 0])
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</div>
