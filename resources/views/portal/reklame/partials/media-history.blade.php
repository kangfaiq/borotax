@php
    $containerClass = $containerClass ?? 'detail-card';
    $sectionClass = $sectionClass ?? 'detail-section';
    $titleClass = $titleClass ?? 'ds-title';
@endphp

@if(($histories ?? collect())->isNotEmpty())
    @if(filled($containerClass))
        <div class="{{ $containerClass }}">
    @endif
        <div class="{{ $sectionClass }}">
            <div class="{{ $titleClass }}">
                <i class="bi {{ $icon ?? 'bi-images' }}"></i> {{ $title }}
            </div>

            <div class="media-history-list">
                @foreach($histories as $history)
                    <div class="media-history-card">
                        <div class="media-history-meta">
                            <div>
                                <div class="media-history-heading">{{ $history['action_label'] }}</div>
                                <div class="media-history-subtext">
                                    {{ $history['created_at']?->translatedFormat('d M Y, H:i') }}
                                    • {{ $history['actor_name'] }}
                                </div>
                            </div>
                            <span class="media-history-label">{{ $history['label'] }}</span>
                        </div>

                        @if(! empty($history['description']))
                            <div class="media-history-description">{{ $history['description'] }}</div>
                        @endif

                        <div class="media-compare-grid">
                            @foreach(['old' => 'Versi Lama', 'new' => 'Versi Baru'] as $side => $sideLabel)
                                @php($version = $history[$side] ?? null)
                                <div class="media-preview-pane">
                                    <div class="media-preview-title">{{ $sideLabel }}</div>

                                    @if($version)
                                        <div class="media-preview-frame">
                                            @if($version['is_image'])
                                                <img src="{{ $version['url'] }}" alt="{{ $sideLabel }} {{ $history['label'] }}" class="media-preview-image">
                                            @elseif($version['is_pdf'])
                                                <div class="media-preview-document">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                    <span>{{ $version['filename'] }}</span>
                                                </div>
                                            @else
                                                <div class="media-preview-document">
                                                    <i class="bi bi-file-earmark"></i>
                                                    <span>{{ $version['filename'] }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="media-preview-actions">
                                            <span class="media-preview-filename">{{ $version['filename'] }}</span>
                                            <a href="{{ $version['url'] }}" target="_blank" class="media-preview-link">
                                                <i class="bi bi-box-arrow-up-right"></i> Buka Preview
                                            </a>
                                        </div>
                                    @else
                                        <div class="media-preview-empty">
                                            <i class="bi bi-dash-circle"></i>
                                            <span>Tidak ada file</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @if(filled($containerClass))
        </div>
    @endif
@endif