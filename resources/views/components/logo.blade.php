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

        <!-- OUTER SHIELD : tetap tajam di atas, tapi lebih lebar & pendek -->
        <path
            d="M80 12 L136 36 V82 C136 112 112 136 80 150 C48 136 24 112 24 82 V36 L80 12Z"
            fill="none"
            class="shield-main"
            stroke-width="10"
            stroke-linejoin="round"
            stroke-linecap="round" />

        <!-- INNER SHIELD -->
        <path
            d="M80 28 L122 46 V80 C122 102 104 122 80 134 C56 122 38 102 38 80 V46 L80 28Z"
            fill="none"
            class="shield-inner"
            stroke-width="5"
            stroke-linejoin="round"
            stroke-linecap="round"
            opacity="0.85" />

        <!-- HEART : lebih modern & balance -->
        <path
            d="M80 112 C62 98 50 84 50 66 C50 54 58 46 70 46 C76 46 80 50 80 50 C80 50 84 46 90 46 C102 46 110 54 110 66 C110 84 98 98 80 112Z"
            class="heart-fill" />

        <!-- ECG -->
        <path
            d="M45 82 H58 L66 70 L80 104 L94 58 L102 82 H115"
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