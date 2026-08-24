<x-filament-widgets::widget>
    <x-filament::card class="p-0">
        {{-- Header & Filters --}}
        <div class="border-b border-gray-200 px-4 py-2 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                    Monitoring Investigasi Insiden
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Daftar insiden dan evaluasi performa investigasi per unit.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center justify-end gap-3 w-full md:w-auto">
                <form action="{{ route('print.monitoring-investigasi') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="unit_id" :value="$wire.unit_id">
                    <input type="hidden" name="quarter" :value="$wire.quarter">
                    <input type="hidden" name="compliance_status" :value="$wire.compliance_status">
                    <input type="hidden" name="month" :value="$wire.month">
                    <input type="hidden" name="year" :value="$wire.year">
                    <input type="hidden" name="sortColumn" :value="$wire.sortColumn">
                    <input type="hidden" name="sortDirection" :value="$wire.sortDirection">
                    <x-filament::button type="submit" color="gray" icon="heroicon-o-printer">
                        Print Laporan
                    </x-filament::button>
                </form>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="unit_id">
                        <option value="">Semua Unit</option>
                        @foreach($this->units as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="quarter">
                        <option value="">Pilih Kuartal</option>
                        @foreach($this->quarters as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="compliance_status">
                        <option value="">Semua Status Waktu</option>
                        <option value="sesuai">Sesuai Target</option>
                        <option value="tidak_sesuai">Tidak Sesuai</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="month">
                        <option value="">Pilih Bulan</option>
                        @foreach($this->months as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="year">
                        @foreach($this->years as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        {{-- Content --}}
        <div class="px-6 py-4 space-y-8">
            
            {{-- Tabel Monitoring Harian --}}
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3">Daftar Insiden & Target Penyelesaian</h3>
                
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 whitespace-nowrap cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800" wire:click="sortBy('unit')">
                                    <div class="flex items-center gap-1">
                                        Unit
                                        @if($sortColumn === 'unit')
                                            <x-filament::icon icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-4 w-4" />
                                        @else
                                            <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-4 w-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 whitespace-nowrap cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800" wire:click="sortBy('raw_tanggal')">
                                    <div class="flex items-center gap-1">
                                        Tanggal
                                        @if($sortColumn === 'raw_tanggal')
                                            <x-filament::icon icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-4 w-4" />
                                        @else
                                            <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-4 w-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 whitespace-nowrap">Jenis Insiden</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700">Kategori Insiden</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Grading</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Target (Hari)</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800" wire:click="sortBy('lama_investigasi')">
                                    <div class="flex items-center justify-center gap-1">
                                        Lama Investigasi
                                        @if($sortColumn === 'lama_investigasi')
                                            <x-filament::icon icon="{{ $sortDirection === 'asc' ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down' }}" class="h-4 w-4" />
                                        @else
                                            <x-filament::icon icon="heroicon-m-chevron-up-down" class="h-4 w-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Kesesuaian Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->incidentsData as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $row->unit }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $row->tanggal }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-gray-200">{{ $row->jenis_insiden }}</td>
                                    <td class="px-4 py-3">{{ $row->insiden }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @php
                                            $colorMap = [
                                                'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'red' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                            ];
                                            $badgeClass = $colorMap[$row->gradingColor] ?? $colorMap['gray'];
                                            
                                            $dotMap = [
                                                'green' => '🟢',
                                                'blue' => '🔵',
                                                'yellow' => '🟡',
                                                'red' => '🔴',
                                                'gray' => '⚪',
                                            ];
                                            $dot = $dotMap[$row->gradingColor] ?? '⚪';
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {{ $badgeClass }}">
                                            <span>{{ $dot }}</span>
                                            <span>{{ $row->grading }}</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium">{{ $row->target_hari }} <span class="text-normal">hari</span></td>
                                    <td class="px-4 py-3 text-center">
                                        @if($row->lama_investigasi !== null)
                                            <span class="font-bold">{{ $row->lama_investigasi }}</span> <span class="text-normal"> hari</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($row->has_started)
                                            @if($row->is_sesuai_target)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                                    Sesuai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                                    Tidak Sesuai
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                                Belum Mulai
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Tidak ada data insiden pada periode yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel Rekapitulasi Unit --}}
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-3">Rekapitulasi Unit</h3>
                
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 whitespace-nowrap">Unit</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Jumlah Insiden</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Sudah Di Investigasi</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Belum Di Investigasi</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Kesesuaian Waktu Pelaporan</th>
                                <th class="px-4 py-3 font-semibold border-b dark:border-gray-700 text-center whitespace-nowrap">Persentase Kesesuaian Laporan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->summaryData as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $row->unit }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-700 dark:text-gray-300">{{ $row->total }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $row->sudah }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $row->belum }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400">
                                            {{ $row->sesuai }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold">
                                        @if($row->persentase >= 80)
                                            <span class="text-indigo-600 dark:text-indigo-400">{{ $row->persentase }}%</span>
                                        @elseif($row->persentase >= 50)
                                            <span class="text-slate-600 dark:text-slate-400">{{ $row->persentase }}%</span>
                                        @else
                                            <span class="text-orange-600 dark:text-orange-400">{{ $row->persentase }}%</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada data rekapitulasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($this->summaryData->count() > 0)
                            <tfoot class="bg-gray-50 dark:bg-gray-800/80 font-bold border-t-2 border-gray-200 dark:border-gray-700">
                                <tr>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">TOTAL</td>
                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $this->summaryData->sum('total') }}</td>
                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $this->summaryData->sum('sudah') }}</td>
                                    <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $this->summaryData->sum('belum') }}</td>
                                    <td class="px-4 py-3 text-center text-indigo-600 dark:text-indigo-400">{{ $this->summaryData->sum('sesuai') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $sumTotal = $this->summaryData->sum('total');
                                            $sumSesuai = $this->summaryData->sum('sesuai');
                                            $avgPercent = $sumTotal > 0 ? round(($sumSesuai / $sumTotal) * 100) : 0;
                                        @endphp
                                        @if($avgPercent >= 80)
                                            <span class="text-indigo-600 dark:text-indigo-400">{{ $avgPercent }}%</span>
                                        @elseif($avgPercent >= 50)
                                            <span class="text-slate-600 dark:text-slate-400">{{ $avgPercent }}%</span>
                                        @else
                                            <span class="text-orange-600 dark:text-orange-400">{{ $avgPercent }}%</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </x-filament::card>
</x-filament-widgets::widget>
