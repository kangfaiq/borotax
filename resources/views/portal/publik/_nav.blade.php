{{-- Shared public menu navigation --}}
<nav class="publik-nav">
    <a href="{{ url('/cek-billing') }}" class="{{ ($active ?? '') === 'cek-billing' ? 'active' : '' }}">
        <i class="bi bi-receipt-cutoff"></i> Cek Billing
    </a>
    <a href="{{ url('/histori-pajak') }}" class="{{ ($active ?? '') === 'histori-pajak' ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i> Histori Pajak
    </a>
    <a href="{{ url('/sewa-reklame') }}" class="{{ ($active ?? '') === 'sewa-reklame' ? 'active' : '' }}">
        <i class="bi bi-signpost-2"></i> Sewa Reklame
    </a>
    <a href="{{ url('/kalkulator-sanksi') }}" class="{{ ($active ?? '') === 'kalkulator-sanksi' ? 'active' : '' }}">
        <i class="bi bi-calculator"></i> Kalkulator Sanksi
    </a>
    <a href="{{ url('/produk-hukum') }}" class="{{ ($active ?? '') === 'produk-hukum' ? 'active' : '' }}">
        <i class="bi bi-bank"></i> Produk Hukum
    </a>
    <a href="{{ url('/kalkulator-air-tanah') }}" class="{{ ($active ?? '') === 'kalkulator-air-tanah' ? 'active' : '' }}">
        <i class="bi bi-droplet"></i> Kalkulator Air Tanah
    </a>
    <a href="{{ url('/kalkulator-reklame') }}" class="{{ ($active ?? '') === 'kalkulator-reklame' ? 'active' : '' }}">
        <i class="bi bi-aspect-ratio"></i> Kalkulator Reklame
    </a>
</nav>
