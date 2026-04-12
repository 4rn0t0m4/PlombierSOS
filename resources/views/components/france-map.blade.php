@props(['departments'])

@php
    // Build a JSON map: dep number (lowercase) => {name, slug}
    $deptMap = $departments->mapWithKeys(fn ($d) => [
        strtolower($d->number) => ['name' => $d->name, 'slug' => $d->slug, 'num' => $d->number],
    ]);
@endphp

<div x-data="franceMap()" x-ref="mapContainer" class="relative max-w-3xl mx-auto">

    {{-- Tooltip --}}
    <div x-show="tooltip" x-cloak x-transition.opacity
         :style="'left:' + tooltipX + 'px; top:' + tooltipY + 'px'"
         class="absolute z-10 bg-blue-900 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow-lg pointer-events-none -translate-x-1/2 -translate-y-full -mt-3 whitespace-nowrap">
        <span x-text="tooltipText"></span>
    </div>

    {{-- SVG Map (from Wikimedia Commons - Public Domain) --}}
    @include('components.france-map-svg')

    {{-- DOM-TOM --}}
    <div class="flex flex-wrap justify-center gap-2 mt-4">
        @foreach(['971' => 'Guadeloupe', '972' => 'Martinique', '973' => 'Guyane', '974' => 'La Réunion', '976' => 'Mayotte'] as $num => $name)
            @php $dept = $departments->firstWhere('number', $num); @endphp
            @if($dept)
                <a href="/{{ $dept->slug }}" class="px-3 py-2 bg-blue-100 hover:bg-blue-500 hover:text-white text-sm rounded transition">
                    {{ $num }} - {{ $dept->name }}
                </a>
            @endif
        @endforeach
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('franceMap', () => ({
        tooltip: false,
        tooltipText: '',
        tooltipX: 0,
        tooltipY: 0,
        depts: @js($deptMap),

        init() {
            // Attach data attributes to all SVG department paths
            this.$refs.mapContainer.querySelectorAll('path[id^="dep_"]').forEach(path => {
                const depNum = path.id.replace('dep_', '');
                const dept = this.depts[depNum];
                if (dept) {
                    path.dataset.dept = depNum;
                    path.dataset.name = dept.name;
                    path.dataset.slug = dept.slug;
                    path.dataset.num = dept.num;
                }
                path.classList.add('cursor-pointer', 'transition-colors');
                path.style.fill = '#bfdbfe'; // blue-200
                path.addEventListener('mouseenter', () => { path.style.fill = '#3b82f6'; }); // blue-500
                path.addEventListener('mouseleave', () => { path.style.fill = '#bfdbfe'; });
            });
        },

        showTooltip(e) {
            const el = e.target.closest('path[data-dept]');
            if (!el) { this.tooltip = false; return; }
            this.tooltipText = el.dataset.num + ' - ' + el.dataset.name;
            const rect = this.$refs.mapContainer.getBoundingClientRect();
            this.tooltipX = e.clientX - rect.left;
            this.tooltipY = e.clientY - rect.top;
            this.tooltip = true;
        },

        hideTooltip() { this.tooltip = false; },

        goToDept(e) {
            const el = e.target.closest('path[data-dept]');
            if (!el || !el.dataset.slug) return;
            window.location.href = '/' + el.dataset.slug;
        }
    }));
});
</script>
