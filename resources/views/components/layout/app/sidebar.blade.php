{{--
    Content of the sidebar: brand, the navigation groups and the deadline card. Rendered twice by the app layout,
    inside the aside (rail between md and lg) and inside the drawer of the phone (full form, without brand).
    <x-layout.app.sidebar :nav="$nav" :deadline="$deadline" :subtitle="$subtitle" />
    <x-layout.app.sidebar :nav="$nav" :deadline="$deadline" :brand="false" :rail="false" />
--}}
@props(['nav' => [], 'deadline' => null, 'subtitle' => null, 'brand' => true, 'rail' => true])
@if ($brand)
    <div class="px-1">
        <x-ui.brand :subtitle="$subtitle" @class(['md:max-lg:hidden' => $rail]) />
        @if ($rail)
            <x-ui.brand mark-only class="hidden md:max-lg:flex" />
        @endif
    </div>
@endif
@foreach ($nav as $group)
    <x-ui.nav :items="$group['items'] ?? []" :heading="$group['label'] ?? null" :label="$group['label'] ?? 'Navegación principal'" :rail="$rail" />
@endforeach
@if ($deadline !== null)
    <div @class(['mt-auto', 'hidden lg:block' => $rail])>
        <x-ui.deadline :label="$deadline['label'] ?? 'Plazo'" :closes-at="$deadline['closesAt'] ?? null" :opens-at="$deadline['opensAt'] ?? null" :href="$deadline['href'] ?? null" />
    </div>
@endif
