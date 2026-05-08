<div class="fi-topbar-ctn">
    @php
    $isRtl = __('filament-panels::layout.direction') === 'rtl';
    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
    $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
    $hasTopNavigation = filament()->hasTopNavigation();
    $hasNavigation = filament()->hasNavigation();
    $hasTenancy = filament()->hasTenancy();
    @endphp

    <nav class="fi-topbar">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START) }}

        @if ($hasNavigation)
        <x-filament::icon-button
            color="gray"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedBars3"
            :icon-alias="\Filament\View\PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON"
            icon-size="lg"
            :label="__('filament-panels::layout.actions.sidebar.expand.label')"
            x-cloak
            x-data="{}"
            x-on:click="$store.sidebar.open()"
            x-show="! $store.sidebar.isOpen"
            class="fi-topbar-open-sidebar-btn" />

        <x-filament::icon-button
            color="gray"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedXMark"
            :icon-alias="\Filament\View\PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON"
            icon-size="lg"
            :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
            x-cloak
            x-data="{}"
            x-on:click="$store.sidebar.close()"
            x-show="$store.sidebar.isOpen"
            class="fi-topbar-close-sidebar-btn" />
        @endif

        <div class="fi-topbar-start mr-2">
            @if ($isSidebarCollapsibleOnDesktop)
            <x-filament::icon-button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::Bars3BottomLeft"
                {{-- @deprecated Use `PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL` instead of `PanelsIconAlias::SIDEBAR_EXPAND_BUTTON` for RTL. --}}
                :icon-alias="
                        $isRtl
                        ? [
                            \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL,
                            \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON,
                        ]
                        : \Filament\View\PanelsIconAlias::SIDEBAR_EXPAND_BUTTON
                    "
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.open()"
                x-show="! $store.sidebar.isOpen"
                class="fi-topbar-open-collapse-sidebar-btn" />
            @endif

            @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
            <x-filament::icon-button
                color="gray"
                :icon="\Filament\Support\Icons\Heroicon::Bars3BottomRight"
                {{-- @deprecated Use `PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL` instead of `PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON` for RTL. --}}
                :icon-alias="
                        $isRtl
                        ? [
                            \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL,
                            \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON,
                        ]
                        : \Filament\View\PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON
                    "
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.close()"
                x-show="$store.sidebar.isOpen"
                class="fi-topbar-close-collapse-sidebar-btn" />
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_LOGO_BEFORE) }}

            @if ($homeUrl = filament()->getHomeUrl())
            <a {{ \Filament\Support\generate_href_html($homeUrl) }}
                class="group flex items-center gap-4 rounded-2xl px-2 py-1 transition-all duration-300 hover:bg-slate-100/70 dark:hover:bg-slate-800/60">

                @props(['width' => '220'])

                <svg xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 820 180"
                    width="{{ $width }}"
                    height="{{ $width * 0.22 }}"
                    role="img"
                    aria-labelledby="logo-title logo-desc"
                    {{ $attributes }}>

                    <title id="logo-title">SI-KP — Sistem Pelaporan Insiden Keselamatan Pasien</title>

                    <desc id="logo-desc">
                        Logo sistem pelaporan insiden keselamatan pasien modern dengan ikon perlindungan pasien dan detak kesehatan.
                    </desc>

                    <defs>
                        <style>
                            /* ===== LIGHT MODE ===== */
                            .shield-main {
                                stroke: #0F766E;
                            }

                            .shield-inner {
                                stroke: #14B8A6;
                            }

                            .pulse-line {
                                stroke: #06B6D4;
                            }

                            .heart-fill {
                                fill: #EF4444;
                            }

                            .text-main {
                                fill: #0F172A;
                            }

                            .text-sub {
                                fill: #475569;
                            }

                            /* ===== DARK MODE ===== */
                            .dark .shield-main {
                                stroke: #2DD4BF;
                            }

                            .dark .shield-inner {
                                stroke: #5EEAD4;
                            }

                            .dark .pulse-line {
                                stroke: #67E8F9;
                            }

                            .dark .heart-fill {
                                fill: #F87171;
                            }

                            .dark .text-main {
                                fill: #F8FAFC;
                            }

                            .dark .text-sub {
                                fill: #CBD5E1;
                            }

                            .font {
                                font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
                            }
                        </style>
                    </defs>
                    <g transform="translate(20,12)">
                        <!-- OUTER SHIELD: Tinggi dikurangi 10%, lebar tetap ramping -->
                        <path d="M80 10 L130 35 V80 C130 115 105 142 80 153 C55 142 30 115 30 80 V35 L80 10Z"
                            fill="none"
                            class="shield-main"
                            stroke-width="10"
                            stroke-linejoin="round" />

                        <!-- INNER SHIELD: Mengikuti pengurangan tinggi outer shield -->
                        <path d="M80 25 L118 42 V78 C118 105 100 128 80 140 C60 128 42 105 42 78 V42 L80 25Z"
                            fill="none"
                            class="shield-inner"
                            stroke-width="5"
                            stroke-linejoin="round"
                            opacity="0.8" />

                        <!-- HEART: Disesuaikan posisinya agar tetap di tengah perisai yang lebih pendek -->
                        <path d="M80 118 C45 93 40 53 62 45 C75 41 80 51 80 51 C80 51 85 41 98 45 C120 53 115 93 80 118Z"
                            class="heart-fill" />

                        <!-- ECG: Disesuaikan sedikit lebih rendah agar presisi dengan jantung -->
                        <path d="M35 83 H52 L62 68 L80 113 L98 53 L108 83 H125"
                            fill="none"
                            class="pulse-line"
                            stroke-width="7"
                            stroke-linecap="round"
                            stroke-linejoin="round" />
                    </g>
                    <!-- ========= TEXT ========= -->
                    <g transform="translate(210,56)">

                        <text x="0"
                            y="44"
                            font-size="64"
                            font-weight="800"
                            letter-spacing="-1.5"
                            class="text-main font">
                            SP-IKP
                        </text>

                        <text x="0"
                            y="84"
                            font-size="25"
                            font-weight="500"
                            class="text-sub font"
                            opacity="0.92">
                            Sistem Pelaporan Insiden Keselamatan Pasien
                        </text>

                    </g>

                </svg>

            </a>
            @else
            <x-filament-panels::logo />
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_LOGO_AFTER) }}
        </div>

        @if ($hasTopNavigation || (! $hasNavigation))
        @if ($hasTenancy && filament()->hasTenantMenu())
        <x-filament-panels::tenant-menu teleport />
        @endif

        @if ($hasNavigation)
        @php
        $navigation = filament()->getNavigation();
        @endphp

        <ul class="fi-topbar-nav-groups">
            @foreach ($navigation as $group)
            @php
            $groupLabel = $group->getLabel();
            $groupExtraTopbarAttributeBag = $group->getExtraTopbarAttributeBag();
            $isGroupActive = $group->isActive();
            $groupIcon = $group->getIcon();
            @endphp

            @if ($groupLabel)
            <x-filament::dropdown
                placement="bottom-start"
                :attributes="\Filament\Support\prepare_inherited_attributes($groupExtraTopbarAttributeBag)">
                <x-slot name="trigger">
                    <x-filament-panels::topbar.item
                        :active="$isGroupActive"
                        :icon="$groupIcon">
                        {{ $groupLabel }}
                    </x-filament-panels::topbar.item>
                </x-slot>

                @php
                $lists = [];

                foreach ($group->getItems() as $item) {
                if ($childItems = $item->getChildItems()) {
                $lists[] = [
                $item,
                ...$childItems,
                ];
                $lists[] = [];

                continue;
                }

                if (empty($lists)) {
                $lists[] = [$item];

                continue;
                }

                $lists[count($lists) - 1][] = $item;
                }

                if (empty($lists[count($lists) - 1])) {
                array_pop($lists);
                }
                @endphp

                @foreach ($lists as $list)
                <x-filament::dropdown.list>
                    @foreach ($list as $item)
                    @php
                    $isItemActive = $item->isActive();
                    $itemBadge = $item->getBadge();
                    $itemBadgeColor = $item->getBadgeColor();
                    $itemBadgeTooltip = $item->getBadgeTooltip();
                    $itemUrl = $item->getUrl();
                    $itemIcon = $isItemActive ? ($item->getActiveIcon() ?? $item->getIcon()) : $item->getIcon();
                    $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
                    @endphp

                    <x-filament::dropdown.list.item
                        :badge="$itemBadge"
                        :badge-color="$itemBadgeColor"
                        :badge-tooltip="$itemBadgeTooltip"
                        :color="$isItemActive ? 'primary' : 'gray'"
                        :href="$itemUrl"
                        :icon="$itemIcon"
                        tag="a"
                        :target="$shouldItemOpenUrlInNewTab ? '_blank' : null">
                        {{ $item->getLabel() }}
                    </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
                @endforeach
            </x-filament::dropdown>
            @else
            @foreach ($group->getItems() as $item)
            @php
            $isItemActive = $item->isActive();
            $itemActiveIcon = $item->getActiveIcon();
            $itemBadge = $item->getBadge();
            $itemBadgeColor = $item->getBadgeColor();
            $itemBadgeTooltip = $item->getBadgeTooltip();
            $itemIcon = $item->getIcon();
            $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
            $itemUrl = $item->getUrl();
            @endphp

            <x-filament-panels::topbar.item
                :active="$isItemActive"
                :active-icon="$itemActiveIcon"
                :badge="$itemBadge"
                :badge-color="$itemBadgeColor"
                :badge-tooltip="$itemBadgeTooltip"
                :icon="$itemIcon"
                :should-open-url-in-new-tab="$shouldItemOpenUrlInNewTab"
                :url="$itemUrl">
                {{ $item->getLabel() }}
            </x-filament-panels::topbar.item>
            @endforeach
            @endif
            @endforeach
        </ul>
        @endif
        @endif

        <div
            @if ($hasTenancy)
            x-persist="topbar.end.panel-{{ filament()->getId() }}.tenant-{{ filament()->getTenant()?->getKey() }}"
            @else
            x-persist="topbar.end.panel-{{ filament()->getId() }}"
            @endif
            class="fi-topbar-end">
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

            @if (filament()->isGlobalSearchEnabled() && filament()->getGlobalSearchPosition() === \Filament\Enums\GlobalSearchPosition::Topbar)
            @livewire(Filament\Livewire\GlobalSearch::class)
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            @if (config('iam.enabled'))
            @livewire('iam-app-switcher')
            @endif

            @if (filament()->auth()->check())
            @if (filament()->hasDatabaseNotifications() && filament()->getDatabaseNotificationsPosition() === \Filament\Enums\DatabaseNotificationsPosition::Topbar)
            @livewire(Filament\Livewire\DatabaseNotifications::class, [
            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
            ])
            @endif

            @if (filament()->hasUserMenu() && filament()->getUserMenuPosition() === \Filament\Enums\UserMenuPosition::Topbar)
            <x-filament-panels::user-menu />
            @endif
            @endif
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_END) }}
    </nav>

    <x-filament-actions::modals />
</div>