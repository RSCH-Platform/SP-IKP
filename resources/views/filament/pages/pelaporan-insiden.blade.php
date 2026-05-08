<x-filament-panels::page>

    {{-- Header Section --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-950">
        <div class="p-6">
            {{-- Hospital Info Header --}}
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-cyan-500 text-white">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Laporan Insiden Internal</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Sistem Pelaporan Insiden Keselamatan Pasien (IKP)</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-200">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Rahasia & Konfidensial
                    </div>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tanggal: {{ now()->format('d F Y') }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-amber-300/60 bg-amber-50/80 dark:border-amber-400/20 dark:bg-amber-900/10">

                <div class="p-5">
                    <div class="flex items-start gap-3">

                        <!-- Icon -->
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.516 11.59c.75 1.335-.213 2.99-1.742 2.99H3.483c-1.53 0-2.492-1.655-1.743-2.99l6.517-11.59zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-6a1 1 0 00-1 1v3a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-amber-700 dark:text-amber-200">
                                Catatan Penting
                            </h3>

                            <div class="mt-3 grid gap-2 text-sm text-amber-800 dark:text-amber-200">

                                <div class="flex items-start gap-2">
                                    <span class="font-semibold">RAHASIA</span>
                                    <span>— Tidak boleh difotocopy & wajib dilaporkan maksimal 2x24 jam</span>
                                </div>

                                <div class="flex items-start gap-2">
                                    <span>Lengkapi semua data secara akurat</span>
                                </div>

                                <div class="flex items-start gap-2">
                                    <span>Bisa disimpan sebagai <strong>Draft</strong></span>
                                </div>

                                <div class="flex items-start gap-2">
                                    <span>Setelah <strong>Submit</strong>, masuk ke proses review</span>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-950">
        <div class="p-6">
            <form wire:submit="submit">
                {{ $this->form }}

                <div class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-700">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Pastikan semua data sudah benar sebelum submit
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($this->getFormActions() as $action)
                            {{ $action }}
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Footer Info --}}
    <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
        <p>Sistem Pelaporan IKP - Informasi dalam laporan ini bersifat rahasia dan hanya untuk keperluan internal</p>
        <p class="mt-1">Untuk bantuan, hubungi Tim Keselamatan Pasien</p>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>