@php
    $defaultLat = $getDefaultLatitude();
    $defaultLng = $getDefaultLongitude();
    $defaultZoom = $getDefaultZoom();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
            map: null,
            marker: null,
            lat: $wire.get('data.latitude') || null,
            lng: $wire.get('data.longitude') || null,
            defaultLat: {{ $defaultLat }},
            defaultLng: {{ $defaultLng }},
            defaultZoom: {{ $defaultZoom }},
            searchQuery: '',
            suggestions: [],
            showSuggestions: false,
            searchTimeout: null,
            selectedIndex: -1,
            isSearching: false,

            init() {
                this.$nextTick(() => {
                    this.initMap();
                });
            },

            initMap() {
                const startLat = this.lat || this.defaultLat;
                const startLng = this.lng || this.defaultLng;
                const startZoom = (this.lat && this.lng) ? 17 : this.defaultZoom;

                this.map = L.map(this.$refs.mapContainer).setView([startLat, startLng], startZoom);

                L.tileLayer('http://mt0.google.com/vt/lyrs=m&hl=en&x={x}&y={y}&z={z}', {
                    attribution: '&copy; Google Maps',
                    maxZoom: 19,
                }).addTo(this.map);

                if (this.lat && this.lng) {
                    this.addMarker(this.lat, this.lng);
                }

                this.map.on('click', (e) => {
                    this.setCoordinates(e.latlng.lat, e.latlng.lng);
                });

                setTimeout(() => this.map.invalidateSize(), 200);
            },

            addMarker(lat, lng) {
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                } else {
                    this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.setCoordinates(pos.lat, pos.lng);
                    });
                }
            },

            setCoordinates(lat, lng) {
                this.lat = parseFloat(lat.toFixed(7));
                this.lng = parseFloat(lng.toFixed(7));
                this.addMarker(this.lat, this.lng);
                $wire.set('data.latitude', this.lat);
                $wire.set('data.longitude', this.lng);
            },

            clearCoordinates() {
                this.lat = null;
                this.lng = null;
                if (this.marker) {
                    this.map.removeLayer(this.marker);
                    this.marker = null;
                }
                $wire.set('data.latitude', null);
                $wire.set('data.longitude', null);
                this.map.setView([this.defaultLat, this.defaultLng], this.defaultZoom);
            },

            onSearchInput() {
                clearTimeout(this.searchTimeout);
                this.selectedIndex = -1;
                const q = this.searchQuery.trim();
                if (q.length < 3) {
                    this.suggestions = [];
                    this.showSuggestions = false;
                    return;
                }
                this.searchTimeout = setTimeout(() => this.fetchSuggestions(q), 350);
            },

            async fetchSuggestions(q) {
                this.isSearching = true;
                try {
                    const request = {
                        input: q,
                        locationRestriction: {
                            west: 111.4,
                            east: 112.2,
                            south: -7.6,
                            north: -6.8,
                        },
                        includedRegionCodes: ['id'],
                    };
                    const { suggestions } = await google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request);
                    this.suggestions = suggestions
                        .filter(s => s.placePrediction)
                        .map(s => ({
                            name: s.placePrediction.text.toString(),
                            placeId: s.placePrediction.placeId,
                            lat: null,
                            lng: null,
                        }));
                    this.showSuggestions = this.suggestions.length > 0;
                } catch (e) {
                    console.error('Places Autocomplete error:', e);
                    this.suggestions = [];
                    this.showSuggestions = false;
                }
                this.isSearching = false;
            },

            selectSuggestion(item) {
                this.searchQuery = item.name.split(',')[0];
                this.showSuggestions = false;
                this.suggestions = [];
                if (item.placeId) {
                    this.getPlaceDetails(item.placeId);
                } else if (item.lat && item.lng) {
                    this.setCoordinates(item.lat, item.lng);
                    this.map.setView([item.lat, item.lng], 17);
                }
            },

            async getPlaceDetails(placeId) {
                try {
                    const place = new google.maps.places.Place({ id: placeId });
                    await place.fetchFields({ fields: ['location'] });
                    if (place.location) {
                        const lat = place.location.lat();
                        const lng = place.location.lng();
                        this.setCoordinates(lat, lng);
                        this.map.setView([lat, lng], 17);
                    }
                } catch (e) {
                    console.error('Place details error:', e);
                }
            },

            handleSearchKeydown(event) {
                if (!this.showSuggestions) return;
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                        this.selectSuggestion(this.suggestions[this.selectedIndex]);
                    } else if (this.suggestions.length > 0) {
                        this.selectSuggestion(this.suggestions[0]);
                    }
                }
            },

            async searchLocation() {
                if (!this.searchQuery.trim()) return;
                if (this.suggestions.length > 0) {
                    this.selectSuggestion(this.suggestions[0]);
                    return;
                }
                try {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode(
                        { address: this.searchQuery, region: 'id' },
                        (results, status) => {
                            if (status === google.maps.GeocoderStatus.OK && results.length > 0) {
                                const lat = results[0].geometry.location.lat();
                                const lng = results[0].geometry.location.lng();
                                this.setCoordinates(lat, lng);
                                this.map.setView([lat, lng], 17);
                            }
                        }
                    );
                } catch (e) {
                    console.error('Geocoding error:', e);
                }
                this.showSuggestions = false;
            },

            useMyLocation() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition((pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.setCoordinates(lat, lng);
                    this.map.setView([lat, lng], 17);
                });
            }
        }" wire:ignore>
        {{-- Leaflet CSS & JS --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        {{-- Google Places API --}}
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwF645NEA8DqmeSw2NBXP2UcQZ5tpmRwg&libraries=places&loading=async"></script>

        {{-- Search bar with autocomplete --}}
        <div style="display: flex; gap: 8px; margin-bottom: 10px; position: relative;">
            <div style="flex: 1; position: relative;">
                <input type="text" x-model="searchQuery"
                    @input="onSearchInput()"
                    @keydown="handleSearchKeydown($event)"
                    @keydown.escape="showSuggestions = false"
                    @click.away="showSuggestions = false"
                    placeholder="Cari lokasi... (contoh: Alun-alun Bojonegoro)"
                    style="width: 100%; padding: 9px 12px; border: 1.5px solid rgba(128,128,128,0.4); border-radius: 8px; font-size: 0.85rem; background: transparent; color: inherit; outline: none;">

                {{-- Loading indicator --}}
                <div x-show="isSearching" x-cloak style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                    <svg class="animate-spin" style="width: 16px; height: 16px; color: var(--gray-400);" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- Suggestions dropdown --}}
                <div x-show="showSuggestions && suggestions.length > 0" x-cloak
                    @click.away="showSuggestions = false"
                    style="position: absolute; top: 100%; left: 0; right: 0; margin-top: 4px; border-radius: 10px; overflow: hidden; z-index: 1000; box-shadow: 0 8px 24px rgba(0,0,0,0.25);"
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-600">
                    <template x-for="(item, index) in suggestions" :key="index">
                        <button type="button"
                            @click="selectSuggestion(item)"
                            @mouseenter="selectedIndex = index"
                            :class="selectedIndex === index
                                ? 'bg-primary-50 dark:bg-primary-900/30'
                                : 'bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800'"
                            class="w-full text-left px-3 py-2.5 border-0 cursor-pointer flex items-start gap-2.5 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors"
                        >
                            <svg class="flex-shrink-0 mt-0.5 w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                            </svg>
                            <span class="text-sm leading-snug font-medium text-gray-800 dark:text-gray-100" x-text="item.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <button type="button" @click="searchLocation()"
                style="padding: 9px 16px; background: var(--primary-600, #4f46e5); color: white; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                </svg>
                Cari
            </button>
            <button type="button" @click="useMyLocation()" title="Gunakan Lokasi Saya"
                style="padding: 9px 12px; background: var(--gray-100); border: 1.5px solid var(--gray-300); border-radius: 8px; cursor: pointer; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--gray-600)"
                    viewBox="0 0 16 16">
                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
                </svg>
            </button>
        </div>

        {{-- Map container --}}
        <div x-ref="mapContainer"
            style="height: 350px; border-radius: 12px; border: 1.5px solid var(--gray-300); overflow: hidden; z-index: 1;">
        </div>

        {{-- Coordinate display --}}
        <div style="margin-top: 10px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <template x-if="lat !== null && lng !== null">
                <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
                    <div
                        style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25); border-radius: 8px; flex: 1;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#22c55e"
                            viewBox="0 0 16 16">
                            <path
                                d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                        </svg>
                        <span style="font-size: 0.82rem; font-weight: 500;">
                            <span style="color: var(--gray-500);">Lat:</span> <span x-text="lat"
                                style="font-weight: 700;"></span>
                            &nbsp;&bull;&nbsp;
                            <span style="color: var(--gray-500);">Lng:</span> <span x-text="lng"
                                style="font-weight: 700;"></span>
                        </span>
                    </div>
                    <button type="button" @click="clearCoordinates()"
                        style="padding: 8px 14px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); border-radius: 8px; color: #ef4444; font-size: 0.78rem; font-weight: 600; cursor: pointer; white-space: nowrap;">
                        Hapus Pin
                    </button>
                </div>
            </template>
            <template x-if="lat === null || lng === null">
                <div
                    style="display: flex; align-items: center; gap: 8px; padding: 8px 14px; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 8px; flex: 1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="var(--gray-400)"
                        viewBox="0 0 16 16">
                        <path
                            d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.399l-.244.012.024-.116 1.658-.281h.476l-.488 2.565zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
                    </svg>
                    <span style="font-size: 0.82rem; color: var(--gray-500);">Klik pada peta untuk menandai lokasi objek
                        pajak</span>
                </div>
            </template>
        </div>
    </div>
</x-dynamic-component>