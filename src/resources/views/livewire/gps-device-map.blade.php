{{-- resources/views/livewire/gps-map.blade.php --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<div
    x-data="gpsMap()"
    x-init="initMap()"
    @markers-updated.window="updateMarkers($event.detail.markers)"
    wire:ignore
    class="w-full rounded-xl overflow-hidden shadow">

    {{-- Filtros --}}
    <div>
        <style>
            .gps-filters {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 16px;
                align-items: end;
            }
            .gps-filters-actions {
                display: flex;
                gap: 8px;
                margin-top: 12px;
                flex-wrap: wrap;
            }
            .gps-label {
                display: block;
                font-size: 12px;
                font-weight: 500;
                color: #6b7280;
                margin-bottom: 6px;
            }
            
            div.gm-style-iw-chr > button {
                color: #6b7280;
            }
        </style>

        <x-filament::section>
            <x-slot name="heading">Filtros de ubicación</x-slot>

            <div class="gps-filters">
                <div>
                    <label class="gps-label">Dispositivo</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="deviceId">
                            <option value="">Todos los dispositivos</option>
                            @foreach(\App\Models\GpsDevice::all() as $device)
                                <option value="{{ $device->id }}">{{ $device->imei }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="gps-label">Desde</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.live="startDate" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="gps-label">Hasta</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model.live="endDate" />
                    </x-filament::input.wrapper>
                </div>

                <x-filament::button wire:click="loadMarkers" icon="heroicon-o-arrow-path">
                    Actualizar
                </x-filament::button>
            </div>

            <div class="gps-filters-actions">
                <x-filament::button wire:click="clearFilters" color="gray" icon="heroicon-o-x-mark" size="sm">
                    Limpiar filtros
                </x-filament::button>
                <x-filament::button wire:click="exportCsv" color="red" icon="heroicon-o-video-camera" size="sm">
                    En Vivo
                </x-filament::button>
                <x-filament::button wire:click="loadLatest" color="success" icon="heroicon-o-clock" size="sm">
                    Últimas posiciones
                </x-filament::button>
            </div>

        </x-filament::section>

        {{-- Panel de estado en tiempo real --}}
        <div
            x-show="alert.visible"
            x-transition
            :class="alert.online ? 'bg-green-50 border-green-400 text-green-800' : 'bg-red-50 border-red-400 text-red-800'"
            class="border-l-4 p-3 rounded mb-2 flex items-center gap-2 text-sm font-medium">
            <span x-text="alert.online ? '🟢' : '🔴'"></span>
            <span x-text="alert.message"></span>
        </div>

        {{-- Mapa --}}
        <div id="gps-map" class="w-full" style="height: 500px;"></div>
    </div>
</div>

@push('scripts')
<script>
    let googleMapsReady = false;
    let pendingInit = null;

    window.initGoogleMaps = function () {
        googleMapsReady = true;
        if (pendingInit) {
            pendingInit();
            pendingInit = null;
        }
    };

    function gpsMap() {
        return {
            map: null,
            markers: {},       // { deviceId: { marker, path, polyline } }
            infoWindow: null,
            routePaths: {},    // { deviceId: [LatLng, ...] }
            liveMode: false,
            alert: { visible: false, message: '', online: true },
            alertTimer: null,

            // ─── Init ────────────────────────────────────────────────────────
            initMap() {
                const init = () => {
                    if (!window.google?.maps) return;

                    this.map = new google.maps.Map(
                        document.getElementById('gps-map'),
                        { center: { lat: 15.34, lng: 44.21 }, zoom: 8 }
                    );

                    this.infoWindow = new google.maps.InfoWindow();

                    // Datos iniciales desde Livewire
                    const initial = @json($markers ?? []);
                    this.updateMarkers(initial);

                    // Suscribirse a WebSockets
                    this.subscribeReverb();
                };

                if (googleMapsReady && window.google?.maps) {
                    init();
                } else {
                    pendingInit = init;
                }
            },

            // ─── WebSockets ──────────────────────────────────────────────────
            subscribeReverb() {
                if (!window.Echo) {
                    console.warn('Laravel Echo no disponible');
                    return;
                }

                // Canal global: recibe TODOS los dispositivos
                window.Echo.channel('vehicles.tracking')
                    .listen('.vehicle.tracking.updated', (data) => {
                        console.log('Ubicación actualizada:', data);
                        this.handleLiveLocation(data);
                    })
                    .listen('.device.status', (data) => {
                        this.handleDeviceStatus(data);
                    });
            },

            // ─── Manejar nueva posición en vivo ──────────────────────────────
            handleLiveLocation(data) {
                if (!window.google?.maps || !this.map) return;

                const position = {
                    lat: Number(data.location.latitude),
                    lng: Number(data.location.longitude),
                };

                const deviceId = data.device.id;
                // ── Marker ──
                if (this.markers[deviceId]) {
                    // mover marker existente con animación suave
                    this.markers[deviceId].marker.setPosition(position);
                } else {
                    // crear nuevo marker
                    const marker = new google.maps.Marker({
                        position,
                        map: this.map,
                        optimized: true,
                        title: data.device.imei,
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                            scaledSize: new google.maps.Size(40, 40),
                        },
                    });

                    marker.addListener('click', () => {
                        this.openInfo(marker, data);
                    });

                    this.markers[deviceId] = { marker };
                }

                // Actualizar infoWindow si ya estaba abierto para ese dispositivo
                this.openInfo(this.markers[deviceId].marker, data);

                // ── Ruta ──
                if (!this.routePaths[deviceId]) {
                    this.routePaths[deviceId] = {
                        path: [],
                        polyline: new google.maps.Polyline({
                            map: this.map,
                            strokeColor: '#2563eb',
                            strokeOpacity: 0.8,
                            strokeWeight: 3,
                        }),
                    };
                }

                const route = this.routePaths[deviceId];
                route.path.push(new google.maps.LatLng(position.lat, position.lng));
                route.polyline.setPath(route.path);

                // Seguir el dispositivo si está en modo vivo
                if (this.liveMode) {
                    this.map.panTo(position);
                }
            },

            // ─── Abrir InfoWindow con datos actualizados ─────────────────────
            openInfo(marker, data) {
                const badge = "<span style='color:#2563eb;font-weight:bold'>📍 En vivo</span>";
                this.infoWindow.setContent(`
                    <div style='color: darkcyan;font-family:sans-serif;min-width:180px;font-size:13px;'>
                        ${badge}<br><br>
                        📟 ${data.device.imei ?? ''}<br>
                        🕐 ${data.location.recorded_at ?? ''}<br>
                        🚗 ${data.location.speed ?? 0} km/h<br>
                        🔋 ${data.device.battery.level ?? 0}%<br>
                        📡 ${data.location.latitude}, ${data.location.longitude}F
                    </div>
                `);
                this.infoWindow.open(this.map, marker);
            },

            // ─── Alerta conexión/desconexión ─────────────────────────────────
            handleDeviceStatus(data) {
                clearTimeout(this.alertTimer);

                this.alert = {
                    visible: true,
                    online:  data.isOnline,
                    message: data.isOnline
                        ? `✅ Dispositivo ${data.imei} conectado`
                        : `❌ Dispositivo ${data.imei} desconectado`,
                };

                // ocultar tras 5 segundos
                this.alertTimer = setTimeout(() => {
                    this.alert.visible = false;
                }, 5000);
            },

            // ─── Cargar markers históricos desde Livewire ────────────────────
            updateMarkers(markers) {
                if (!window.google?.maps || !this.map) return;

                // limpiar markers históricos (no los de socket)
                Object.values(this.markers).forEach(({ marker }) => marker.setMap(null));
                this.markers = {};

                // limpiar rutas
                Object.values(this.routePaths).forEach(({ polyline }) => polyline.setMap(null));
                this.routePaths = {};

                if (!Array.isArray(markers) || markers.length === 0) return;

                const bounds = new google.maps.LatLngBounds();

                markers.forEach((data) => {
                    const position = {
                        lat: Number(data.lat),
                        lng: Number(data.lng),
                    };

                    const marker = new google.maps.Marker({
                        position,
                        map: this.map,
                        optimized: true,
                        icon: {
                            url: data.isLatest
                                ? 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                                : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                            scaledSize: new google.maps.Size(
                                data.isLatest ? 40 : 24,
                                data.isLatest ? 40 : 24
                            ),
                        },
                    });

                    marker.addListener('click', () => {
                        this.openInfo(marker, data);
                    });

                    if (data.isLatest) {
                        this.markers[data.deviceId] = { marker };
                    }

                    bounds.extend(position);
                });

                if (!bounds.isEmpty()) {
                    this.map.fitBounds(bounds);
                    const listener = google.maps.event.addListener(this.map, 'idle', () => {
                        if (this.map.getZoom() > 18) this.map.setZoom(18);
                        google.maps.event.removeListener(listener);
                    });
                }
            },
        };
    }
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAhzJmJd8qBcrDjZY_uKr5OGXkAN-eq-Q&callback=initGoogleMaps"
    async
    defer></script>
@endpush