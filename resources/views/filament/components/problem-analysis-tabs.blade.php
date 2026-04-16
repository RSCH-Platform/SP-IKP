{{-- 
  Problem Analysis Tabs Component
  Displays problem analysis data in a tabbed interface
  - Tab 1: WHY Analysis (grid/table format)
  - Tab 2: Contributing Factors (grid format)
  - Tab 3: Recommendations (grid format)
  - Tab 4: Corrective Actions (grid format)
  
  Props passed:
  - $formComponent: Filament form state
--}}

<div wire:key="problem-tabs-{{ rand() }}" x-data="problemTabs()" class="space-y-4">
    {{-- Sticky Header: Problem Type & Description --}}
    <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200 px-6 py-4 rounded-t-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Problem Type Badge --}}
            <div>
                <div class="text-xs font-semibold text-gray-600 mb-1">🏷️ Jenis Masalah</div>
                <div class="inline-block px-4 py-2 rounded-full font-semibold text-white"
                    :class="{'bg-red-500': problem_type === 'CMP', 'bg-orange-500': problem_type === 'SDP'}"
                >
                    <span x-text="problem_type || 'N/A'"></span>
                </div>
                <p class="text-xs text-gray-500 mt-2 italic">
                    💡 Ubah di Timeline section jika ingin mengubah jenis masalah
                </p>
            </div>

            {{-- Description Preview --}}
            <div>
                <div class="text-xs font-semibold text-gray-600 mb-1">📝 Deskripsi Masalah (dari Timeline)</div>
                <p class="text-sm text-gray-700 p-3 bg-white rounded border border-gray-200 max-h-20 overflow-y-auto">
                    <span x-text="problem_description || '(Belum ada deskripsi)'"></span>
                </p>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-lg overflow-x-auto">
        <button @click="activeTab = 'whys'" 
            :class="activeTab === 'whys' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
            class="flex-1 px-4 py-2 rounded font-semibold text-sm whitespace-nowrap transition"
        >
            📊 Analisa WHY
        </button>
        <button @click="activeTab = 'contributors'"
            :class="activeTab === 'contributors' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
            class="flex-1 px-4 py-2 rounded font-semibold text-sm whitespace-nowrap transition"
        >
            🎯 Faktor Kontributor
        </button>
        <button @click="activeTab = 'recommendations'"
            :class="activeTab === 'recommendations' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
            class="flex-1 px-4 py-2 rounded font-semibold text-sm whitespace-nowrap transition"
        >
            💡 Rekomendasi
        </button>
        <button @click="activeTab = 'actions'"
            :class="activeTab === 'actions' ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
            class="flex-1 px-4 py-2 rounded font-semibold text-sm whitespace-nowrap transition"
        >
            ✅ Tindakan
        </button>
    </div>

    {{-- Tab Contents --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        
        {{-- TAB 1: WHY Analysis --}}
        <div x-show="activeTab === 'whys'" x-transition class="p-6">
            <div class="space-y-2 mb-4">
                <h3 class="text-sm font-semibold text-gray-800">📊 Analisa WHY (Akar Masalah)</h3>
                <p class="text-xs text-gray-600">Turunkan akar masalah dengan metode 5 WHY. Maksimal 5 level.</p>
            </div>

            {{-- WHYs Table/Grid --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 w-12">Level</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Pertanyaan WHY</th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        {{-- WHYs akan ditampilkan di sini oleh Filament repeater --}}
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                <p class="text-xs">Repeater component akan render WHYs di sini</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: Contributing Factors --}}
        <div x-show="activeTab === 'contributors'" x-transition class="p-6">
            <div class="space-y-2 mb-4">
                <h3 class="text-sm font-semibold text-gray-800">🎯 Faktor Kontributor (5M: Man, Method, Machine, Material, Medium)</h3>
                <p class="text-xs text-gray-600">Identifikasi faktor-faktor yang berkontribusi terhadap masalah. Pilih kategori → komponen → sub-komponen.</p>
            </div>

            {{-- Contributors Grid --}}
            <div class="space-y-2">
                {{-- Header --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-2 mb-2 pb-2 border-b-2 border-gray-300">
                    <div class="lg:col-span-4">
                        <p class="text-xs font-semibold text-gray-600">Kategori > Komponen > Sub-Komponen</p>
                    </div>
                    <div class="lg:col-span-7">
                        <p class="text-xs font-semibold text-gray-600">Deskripsi</p>
                    </div>
                    <div class="lg:col-span-1">
                        <p class="text-xs font-semibold text-gray-600 text-center">Aksi</p>
                    </div>
                </div>

                {{-- Repeater component akan render contributors di sini --}}
                <div class="text-center py-8 text-gray-500">
                    <p class="text-xs">Repeater component akan render contributors di sini</p>
                </div>
            </div>
        </div>

        {{-- TAB 3: Recommendations --}}
        <div x-show="activeTab === 'recommendations'" x-transition class="p-6">
            <div class="space-y-2 mb-4">
                <h3 class="text-sm font-semibold text-gray-800">💡 Rekomendasi Perbaikan</h3>
                <p class="text-xs text-gray-600">Rekomendasikan tindakan korektif dan preventif dengan prioritas.</p>
            </div>

            {{-- Recommendations Grid --}}
            <div class="space-y-2">
                {{-- Header --}}
                <div class="grid grid-cols-1 md:grid-cols-6 gap-2 mb-2 pb-2 border-b-2 border-gray-300">
                    <div class="md:col-span-4">
                        <p class="text-xs font-semibold text-gray-600">Rekomendasi</p>
                    </div>
                    <div class="md:col-span-1">
                        <p class="text-xs font-semibold text-gray-600">Prioritas</p>
                    </div>
                    <div class="md:col-span-1">
                        <p class="text-xs font-semibold text-gray-600 text-center">Aksi</p>
                    </div>
                </div>

                {{-- Repeater component akan render recommendations di sini --}}
                <div class="text-center py-8 text-gray-500">
                    <p class="text-xs">Repeater component akan render recommendations di sini</p>
                </div>
            </div>
        </div>

        {{-- TAB 4: Corrective Actions --}}
        <div x-show="activeTab === 'actions'" x-transition class="p-6">
            <div class="space-y-2 mb-4">
                <h3 class="text-sm font-semibold text-gray-800">✅ Tindakan Korektif & Preventif</h3>
                <p class="text-xs text-gray-600">Tetapkan tindakan, penanggung jawab, deadline, dan status. Upload bukti pelaksanaan.</p>
            </div>

            {{-- Actions Grid with Status Badges --}}
            <div class="space-y-2">
                {{-- Header --}}
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-2 mb-2 pb-2 border-b-2 border-gray-300">
                    <div class="xl:col-span-3">
                        <p class="text-xs font-semibold text-gray-600">Tindakan</p>
                    </div>
                    <div class="xl:col-span-2">
                        <p class="text-xs font-semibold text-gray-600">PJ</p>
                    </div>
                    <div class="xl:col-span-2">
                        <p class="text-xs font-semibold text-gray-600">Deadline</p>
                    </div>
                    <div class="xl:col-span-2">
                        <p class="text-xs font-semibold text-gray-600">Status</p>
                    </div>
                    <div class="xl:col-span-2">
                        <p class="text-xs font-semibold text-gray-600">Bukti / Aksi</p>
                    </div>
                    <div class="xl:col-span-1">
                        <p class="text-xs font-semibold text-gray-600 text-center">Aksi</p>
                    </div>
                </div>

                {{-- Repeater component akan render actions di sini --}}
                <div class="text-center py-8 text-gray-500">
                    <p class="text-xs">Repeater component akan render actions di sini</p>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Alpine.js Component --}}
<script>
    function problemTabs() {
        return {
            activeTab: 'whys',
            problem_type: @js($problem_type ?? 'N/A'),
            problem_description: @js($problem_description ?? ''),
        }
    }
</script>
