<!-- Sidebar -->
@php
$menus = [
    [
        'heading' => 'Core',
        'items' => [
            ['label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'route' => 'admin.dashboard'],
        ]
    ],
    [
        'heading' => 'Peran Admin',
        'items' => [
            [
                'label' => 'Verifikasi Berkas',
                'icon' => 'fas fa-columns',
                'children' => [
                    ['label' => 'Daftar Pengajuan', 'route' => 'admin.verif.daftar_pengajuan'],
                    ['label' => 'Lihat Hasil Verifikasi', 'route' => 'admin.verif.hasil_verifikasi'],
                ]
            ],
            [
                'label' => 'Kirim SK UKK',
                'icon' => 'fas fa-book-open',
                'children' => [
                    ['label' => 'Upload SK UKK', 'route' => 'admin.skukk.create'],
                    ['label' => 'Lihat SK UKK', 'route' => 'admin.skukk.index'],
                ]
            ],
            [
                'label' => 'Kelola Persyaratan',
                'icon' => 'fas fa-columns',
                'children' => [
                    ['label' => 'Tambah Persyaratan', 'route' => 'admin.syarat.tambah_syarat'],
                    ['label' => 'Lihat Persyaratan', 'route' => 'admin.syarat.lihat_syarat'],
                ]
            ]
        ]
    ]
];
@endphp

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sidebar-gradient" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                @foreach($menus as $menu)
                    <div class="sb-sidenav-menu-heading">{{ $menu['heading'] }}</div>

                    @foreach($menu['items'] as $item)
                        @if(isset($item['children']))
                            @php
                                $isActive = collect($item['children'])->contains(fn($child) => Request::routeIs($child['route']));
                            @endphp
                            <a class="nav-link {{ $isActive ? '' : 'collapsed' }}"
                               href="#"
                               data-bs-toggle="collapse"
                               data-bs-target="#collapse{{ \Illuminate\Support\Str::slug($item['label']) }}"
                               aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                                <div class="sb-nav-link-icon"><i class="{{ $item['icon'] }}"></i></div>
                                {{ $item['label'] }}
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse {{ $isActive ? 'show' : '' }}"
                                 id="collapse{{ \Illuminate\Support\Str::slug($item['label']) }}"
                                 data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    @foreach($item['children'] as $child)
                                        <a class="nav-link {{ Request::routeIs($child['route']) ? 'active' : '' }}"
                                           href="{{ route($child['route']) }}">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </nav>
                            </div>
                        @else
                            <a class="nav-link {{ Request::routeIs($item['route']) ? 'active' : '' }}"
                               href="{{ route($item['route']) }}">
                                <div class="sb-nav-link-icon"><i class="{{ $item['icon'] }}"></i></div>
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </nav>
</div>
