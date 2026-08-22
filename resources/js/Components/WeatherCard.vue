<script setup>
import { CloudSun, LocateFixed, RefreshCw, Wind } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const loading = ref(false);
const error = ref('');
const weather = ref(null);
const location = ref(null);

const weatherLabels = {
    0: 'Clear sky',
    1: 'Mainly clear',
    2: 'Partly cloudy',
    3: 'Overcast',
    45: 'Foggy',
    48: 'Rime fog',
    51: 'Light drizzle',
    53: 'Drizzle',
    55: 'Heavy drizzle',
    61: 'Light rain',
    63: 'Rain',
    65: 'Heavy rain',
    71: 'Light snow',
    73: 'Snow',
    75: 'Heavy snow',
    80: 'Rain showers',
    81: 'Rain showers',
    82: 'Heavy rain showers',
    95: 'Thunderstorm',
    96: 'Thunderstorm with hail',
    99: 'Thunderstorm with hail',
};

const description = computed(() => weatherLabels[weather.value?.weather_code] || 'Current conditions');
const temperature = computed(() => weather.value ? `${Math.round(weather.value.temperature_2m)}°C` : '--');
const coordinates = computed(() => location.value ? `${location.value.latitude.toFixed(4)}, ${location.value.longitude.toFixed(4)}` : 'Location permission required');

const loadWeather = () => {
    loading.value = true;
    error.value = '';

    if (!navigator.geolocation) {
        error.value = 'Location is not supported by this browser.';
        loading.value = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(async (position) => {
        location.value = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
        };

        try {
            const params = new URLSearchParams({
                latitude: location.value.latitude,
                longitude: location.value.longitude,
                current: 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m',
                timezone: 'auto',
            });
            const response = await fetch(`https://api.open-meteo.com/v1/forecast?${params}`);
            if (!response.ok) throw new Error('Weather service unavailable.');
            const data = await response.json();
            weather.value = data.current;
        } catch (requestError) {
            error.value = requestError.message || 'Unable to load weather.';
        } finally {
            loading.value = false;
        }
    }, (geolocationError) => {
        error.value = geolocationError.code === 1
            ? 'Allow location access to show local weather.'
            : 'Unable to determine your location.';
        loading.value = false;
    }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 });
};

onMounted(loadWeather);
</script>

<template>
    <section class="weather-card rounded-xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-sky-700">Local weather</p>
                <h2 class="mt-1 text-base font-semibold text-slate-900">Weather near your location</h2>
                <p class="mt-1 flex items-center gap-1 text-xs text-slate-500"><LocateFixed class="h-3.5 w-3.5" /> {{ coordinates }}</p>
            </div>
            <CloudSun class="h-9 w-9 text-sky-600" aria-hidden="true" />
        </div>

        <div v-if="loading" class="mt-5 flex items-center gap-2 text-sm text-slate-600"><RefreshCw class="h-4 w-4 animate-spin" /> Loading current conditions...</div>
        <div v-else-if="weather" class="mt-5 flex flex-wrap items-end gap-x-6 gap-y-3">
            <div><p class="text-4xl font-semibold tracking-tight text-slate-900">{{ temperature }}</p><p class="text-sm font-medium text-sky-800">{{ description }}</p></div>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-slate-600"><div><dt>Feels like</dt><dd class="font-semibold text-slate-900">{{ Math.round(weather.apparent_temperature) }}°C</dd></div><div><dt>Humidity</dt><dd class="font-semibold text-slate-900">{{ weather.relative_humidity_2m }}%</dd></div><div><dt>Wind</dt><dd class="flex items-center gap-1 font-semibold text-slate-900"><Wind class="h-3.5 w-3.5" /> {{ Math.round(weather.wind_speed_10m) }} km/h</dd></div></dl>
        </div>
        <div v-else class="mt-5 flex items-center justify-between gap-3"><p class="text-sm text-slate-600">{{ error }}</p><button type="button" @click="loadWeather" class="inline-flex shrink-0 items-center gap-1 bg-sky-600 px-3 py-2 text-xs text-white hover:bg-sky-700"><RefreshCw class="h-3.5 w-3.5" /> Retry</button></div>
    </section>
</template>
