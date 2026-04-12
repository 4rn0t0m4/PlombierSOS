@props(['departments'])

@php
    // Index departments by number for quick lookup
    $deptIndex = $departments->keyBy('number');
@endphp

<div x-data="{
    tooltip: false,
    tooltipText: '',
    tooltipX: 0,
    tooltipY: 0,
    showTooltip(e) {
        const el = e.target.closest('[data-dept]');
        if (!el) return;
        this.tooltipText = el.dataset.name;
        const rect = this.$refs.mapContainer.getBoundingClientRect();
        this.tooltipX = e.clientX - rect.left;
        this.tooltipY = e.clientY - rect.top;
        this.tooltip = true;
    },
    hideTooltip() {
        this.tooltip = false;
    },
    goToDept(e) {
        const el = e.target.closest('[data-dept]');
        if (!el || !el.dataset.slug) return;
        window.location.href = '/' + el.dataset.slug;
    }
}" x-ref="mapContainer" class="relative max-w-3xl mx-auto">

    {{-- Tooltip --}}
    <div x-show="tooltip" x-cloak x-transition.opacity
         :style="'left:' + tooltipX + 'px; top:' + tooltipY + 'px'"
         class="absolute z-10 bg-blue-900 text-white text-sm font-medium px-3 py-1.5 rounded-lg shadow-lg pointer-events-none -translate-x-1/2 -translate-y-full -mt-3 whitespace-nowrap">
        <span x-text="tooltipText"></span>
        <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-[6px] border-r-[6px] border-t-[6px] border-l-transparent border-r-transparent border-t-blue-900"></div>
    </div>

    {{-- SVG Map of Metropolitan France --}}
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 960" class="w-full h-auto"
         @mousemove="showTooltip($event)" @mouseleave="hideTooltip()" @click="goToDept($event)">
        <style>
            .dept { fill: #dbeafe; stroke: #1e3a5f; stroke-width: 0.8; cursor: pointer; transition: fill 0.15s ease; }
            .dept:hover { fill: #3b82f6; }
        </style>

        {{-- 01 - Ain --}}
        <path class="dept" d="M672,478 L688,462 L702,468 L718,456 L726,464 L734,458 L742,470 L738,486 L726,498 L718,510 L706,516 L694,510 L684,518 L674,510 L668,498 L672,486 Z"
              data-dept="{{ $deptIndex->has('01') ? '01' : '' }}" data-name="{{ $deptIndex->has('01') ? $deptIndex['01']->name : 'Ain' }}" data-slug="{{ $deptIndex->has('01') ? $deptIndex['01']->slug : '' }}" />

        {{-- 02 - Aisne --}}
        <path class="dept" d="M510,130 L528,118 L548,122 L562,114 L576,120 L584,132 L578,146 L570,158 L556,164 L542,170 L528,166 L514,158 L506,146 L510,134 Z"
              data-dept="{{ $deptIndex->has('02') ? '02' : '' }}" data-name="{{ $deptIndex->has('02') ? $deptIndex['02']->name : 'Aisne' }}" data-slug="{{ $deptIndex->has('02') ? $deptIndex['02']->slug : '' }}" />

        {{-- 03 - Allier --}}
        <path class="dept" d="M518,470 L536,458 L554,462 L570,456 L582,464 L586,478 L580,492 L568,502 L554,508 L538,504 L524,496 L516,484 Z"
              data-dept="{{ $deptIndex->has('03') ? '03' : '' }}" data-name="{{ $deptIndex->has('03') ? $deptIndex['03']->name : 'Allier' }}" data-slug="{{ $deptIndex->has('03') ? $deptIndex['03']->slug : '' }}" />

        {{-- 04 - Alpes-de-Haute-Provence --}}
        <path class="dept" d="M714,658 L730,644 L748,648 L764,640 L776,652 L780,668 L772,682 L758,690 L742,686 L728,692 L716,682 L712,668 Z"
              data-dept="{{ $deptIndex->has('04') ? '04' : '' }}" data-name="{{ $deptIndex->has('04') ? $deptIndex['04']->name : 'Alpes-de-Haute-Provence' }}" data-slug="{{ $deptIndex->has('04') ? $deptIndex['04']->slug : '' }}" />

        {{-- 05 - Hautes-Alpes --}}
        <path class="dept" d="M742,608 L758,596 L774,600 L786,594 L796,604 L798,618 L790,630 L776,636 L762,632 L748,638 L738,628 L736,616 Z"
              data-dept="{{ $deptIndex->has('05') ? '05' : '' }}" data-name="{{ $deptIndex->has('05') ? $deptIndex['05']->name : 'Hautes-Alpes' }}" data-slug="{{ $deptIndex->has('05') ? $deptIndex['05']->slug : '' }}" />

        {{-- 06 - Alpes-Maritimes --}}
        <path class="dept" d="M800,666 L816,654 L832,658 L846,652 L856,664 L858,680 L848,692 L834,696 L818,690 L806,696 L796,684 L798,672 Z"
              data-dept="{{ $deptIndex->has('06') ? '06' : '' }}" data-name="{{ $deptIndex->has('06') ? $deptIndex['06']->name : 'Alpes-Maritimes' }}" data-slug="{{ $deptIndex->has('06') ? $deptIndex['06']->slug : '' }}" />

        {{-- 07 - Ardèche --}}
        <path class="dept" d="M636,570 L650,556 L664,560 L674,554 L682,566 L680,582 L672,594 L658,600 L644,596 L634,602 L628,590 L630,578 Z"
              data-dept="{{ $deptIndex->has('07') ? '07' : '' }}" data-name="{{ $deptIndex->has('07') ? $deptIndex['07']->name : 'Ardèche' }}" data-slug="{{ $deptIndex->has('07') ? $deptIndex['07']->slug : '' }}" />

        {{-- 08 - Ardennes --}}
        <path class="dept" d="M560,96 L576,82 L594,86 L608,80 L618,92 L616,108 L608,120 L594,126 L578,122 L566,128 L558,116 L556,104 Z"
              data-dept="{{ $deptIndex->has('08') ? '08' : '' }}" data-name="{{ $deptIndex->has('08') ? $deptIndex['08']->name : 'Ardennes' }}" data-slug="{{ $deptIndex->has('08') ? $deptIndex['08']->slug : '' }}" />

        {{-- 09 - Ariège --}}
        <path class="dept" d="M378,788 L396,776 L414,780 L428,774 L438,786 L436,802 L426,814 L412,818 L396,812 L382,818 L374,806 L374,794 Z"
              data-dept="{{ $deptIndex->has('09') ? '09' : '' }}" data-name="{{ $deptIndex->has('09') ? $deptIndex['09']->name : 'Ariège' }}" data-slug="{{ $deptIndex->has('09') ? $deptIndex['09']->slug : '' }}" />

        {{-- 10 - Aube --}}
        <path class="dept" d="M538,218 L556,206 L574,210 L588,204 L598,216 L596,232 L586,244 L572,250 L556,246 L542,252 L534,238 L532,226 Z"
              data-dept="{{ $deptIndex->has('10') ? '10' : '' }}" data-name="{{ $deptIndex->has('10') ? $deptIndex['10']->name : 'Aube' }}" data-slug="{{ $deptIndex->has('10') ? $deptIndex['10']->slug : '' }}" />

        {{-- 11 - Aude --}}
        <path class="dept" d="M444,748 L462,736 L480,740 L496,734 L506,746 L504,762 L494,774 L478,778 L462,772 L448,778 L438,766 L438,754 Z"
              data-dept="{{ $deptIndex->has('11') ? '11' : '' }}" data-name="{{ $deptIndex->has('11') ? $deptIndex['11']->name : 'Aude' }}" data-slug="{{ $deptIndex->has('11') ? $deptIndex['11']->slug : '' }}" />

        {{-- 12 - Aveyron --}}
        <path class="dept" d="M498,638 L518,624 L538,628 L554,620 L566,632 L564,650 L554,664 L538,670 L520,666 L504,672 L494,658 L492,646 Z"
              data-dept="{{ $deptIndex->has('12') ? '12' : '' }}" data-name="{{ $deptIndex->has('12') ? $deptIndex['12']->name : 'Aveyron' }}" data-slug="{{ $deptIndex->has('12') ? $deptIndex['12']->slug : '' }}" />

        {{-- 13 - Bouches-du-Rhône --}}
        <path class="dept" d="M676,714 L694,702 L714,706 L730,698 L742,710 L740,728 L730,742 L714,748 L696,744 L680,750 L670,736 L670,722 Z"
              data-dept="{{ $deptIndex->has('13') ? '13' : '' }}" data-name="{{ $deptIndex->has('13') ? $deptIndex['13']->name : 'Bouches-du-Rhône' }}" data-slug="{{ $deptIndex->has('13') ? $deptIndex['13']->slug : '' }}" />

        {{-- 14 - Calvados --}}
        <path class="dept" d="M310,186 L330,174 L352,178 L370,172 L380,184 L376,200 L364,212 L348,216 L330,210 L314,216 L304,204 L304,192 Z"
              data-dept="{{ $deptIndex->has('14') ? '14' : '' }}" data-name="{{ $deptIndex->has('14') ? $deptIndex['14']->name : 'Calvados' }}" data-slug="{{ $deptIndex->has('14') ? $deptIndex['14']->slug : '' }}" />

        {{-- 15 - Cantal --}}
        <path class="dept" d="M498,570 L516,558 L534,562 L548,556 L558,568 L556,584 L546,596 L530,600 L514,596 L500,602 L490,588 L492,576 Z"
              data-dept="{{ $deptIndex->has('15') ? '15' : '' }}" data-name="{{ $deptIndex->has('15') ? $deptIndex['15']->name : 'Cantal' }}" data-slug="{{ $deptIndex->has('15') ? $deptIndex['15']->slug : '' }}" />

        {{-- 16 - Charente --}}
        <path class="dept" d="M346,490 L364,478 L382,482 L396,476 L406,488 L404,504 L394,516 L378,520 L362,516 L348,522 L338,508 L340,496 Z"
              data-dept="{{ $deptIndex->has('16') ? '16' : '' }}" data-name="{{ $deptIndex->has('16') ? $deptIndex['16']->name : 'Charente' }}" data-slug="{{ $deptIndex->has('16') ? $deptIndex['16']->slug : '' }}" />

        {{-- 17 - Charente-Maritime --}}
        <path class="dept" d="M286,490 L304,478 L322,482 L338,476 L348,488 L346,504 L336,518 L320,524 L302,520 L286,526 L276,512 L278,498 Z"
              data-dept="{{ $deptIndex->has('17') ? '17' : '' }}" data-name="{{ $deptIndex->has('17') ? $deptIndex['17']->name : 'Charente-Maritime' }}" data-slug="{{ $deptIndex->has('17') ? $deptIndex['17']->slug : '' }}" />

        {{-- 18 - Cher --}}
        <path class="dept" d="M480,390 L498,378 L516,382 L532,376 L542,388 L540,404 L530,418 L514,424 L498,420 L482,426 L474,412 L474,398 Z"
              data-dept="{{ $deptIndex->has('18') ? '18' : '' }}" data-name="{{ $deptIndex->has('18') ? $deptIndex['18']->name : 'Cher' }}" data-slug="{{ $deptIndex->has('18') ? $deptIndex['18']->slug : '' }}" />

        {{-- 19 - Corrèze --}}
        <path class="dept" d="M444,540 L462,528 L480,532 L494,526 L504,538 L502,554 L492,566 L476,570 L460,566 L446,572 L436,558 L438,546 Z"
              data-dept="{{ $deptIndex->has('19') ? '19' : '' }}" data-name="{{ $deptIndex->has('19') ? $deptIndex['19']->name : 'Corrèze' }}" data-slug="{{ $deptIndex->has('19') ? $deptIndex['19']->slug : '' }}" />

        {{-- 2A - Corse-du-Sud --}}
        <path class="dept" d="M892,796 L904,784 L918,788 L928,782 L936,794 L934,808 L924,818 L912,822 L900,818 L890,824 L884,812 L886,800 Z"
              data-dept="{{ $deptIndex->has('2A') ? '2A' : '' }}" data-name="{{ $deptIndex->has('2A') ? $deptIndex['2A']->name : 'Corse-du-Sud' }}" data-slug="{{ $deptIndex->has('2A') ? $deptIndex['2A']->slug : '' }}" />

        {{-- 2B - Haute-Corse --}}
        <path class="dept" d="M898,740 L912,728 L926,732 L938,726 L946,738 L944,754 L934,764 L920,768 L906,764 L894,770 L886,756 L890,744 Z"
              data-dept="{{ $deptIndex->has('2B') ? '2B' : '' }}" data-name="{{ $deptIndex->has('2B') ? $deptIndex['2B']->name : 'Haute-Corse' }}" data-slug="{{ $deptIndex->has('2B') ? $deptIndex['2B']->slug : '' }}" />

        {{-- 21 - Côte-d'Or --}}
        <path class="dept" d="M596,330 L616,316 L636,320 L654,314 L664,328 L660,346 L648,360 L632,366 L614,360 L598,366 L588,350 L590,338 Z"
              data-dept="{{ $deptIndex->has('21') ? '21' : '' }}" data-name="{{ $deptIndex->has('21') ? $deptIndex['21']->name : &quot;Côte-d'Or&quot; }}" data-slug="{{ $deptIndex->has('21') ? $deptIndex['21']->slug : '' }}" />

        {{-- 22 - Côtes-d'Armor --}}
        <path class="dept" d="M174,218 L194,206 L216,210 L234,204 L244,216 L240,232 L228,244 L212,248 L194,244 L178,250 L168,236 L168,224 Z"
              data-dept="{{ $deptIndex->has('22') ? '22' : '' }}" data-name="{{ $deptIndex->has('22') ? $deptIndex['22']->name : &quot;Côtes-d'Armor&quot; }}" data-slug="{{ $deptIndex->has('22') ? $deptIndex['22']->slug : '' }}" />

        {{-- 23 - Creuse --}}
        <path class="dept" d="M444,488 L462,476 L480,480 L494,474 L504,486 L502,502 L492,514 L476,518 L460,514 L446,520 L436,506 L438,494 Z"
              data-dept="{{ $deptIndex->has('23') ? '23' : '' }}" data-name="{{ $deptIndex->has('23') ? $deptIndex['23']->name : 'Creuse' }}" data-slug="{{ $deptIndex->has('23') ? $deptIndex['23']->slug : '' }}" />

        {{-- 24 - Dordogne --}}
        <path class="dept" d="M382,548 L402,534 L422,538 L438,530 L450,542 L448,560 L438,574 L422,580 L404,576 L386,582 L376,568 L376,554 Z"
              data-dept="{{ $deptIndex->has('24') ? '24' : '' }}" data-name="{{ $deptIndex->has('24') ? $deptIndex['24']->name : 'Dordogne' }}" data-slug="{{ $deptIndex->has('24') ? $deptIndex['24']->slug : '' }}" />

        {{-- 25 - Doubs --}}
        <path class="dept" d="M700,358 L718,344 L738,348 L754,340 L766,352 L764,370 L754,384 L738,390 L720,386 L704,392 L694,376 L694,364 Z"
              data-dept="{{ $deptIndex->has('25') ? '25' : '' }}" data-name="{{ $deptIndex->has('25') ? $deptIndex['25']->name : 'Doubs' }}" data-slug="{{ $deptIndex->has('25') ? $deptIndex['25']->slug : '' }}" />

        {{-- 26 - Drôme --}}
        <path class="dept" d="M672,600 L690,586 L706,590 L720,584 L730,596 L728,614 L718,628 L702,634 L686,630 L672,636 L664,622 L666,608 Z"
              data-dept="{{ $deptIndex->has('26') ? '26' : '' }}" data-name="{{ $deptIndex->has('26') ? $deptIndex['26']->name : 'Drôme' }}" data-slug="{{ $deptIndex->has('26') ? $deptIndex['26']->slug : '' }}" />

        {{-- 27 - Eure --}}
        <path class="dept" d="M390,182 L410,168 L430,172 L448,164 L458,178 L454,196 L442,210 L426,216 L408,210 L392,216 L382,200 L384,188 Z"
              data-dept="{{ $deptIndex->has('27') ? '27' : '' }}" data-name="{{ $deptIndex->has('27') ? $deptIndex['27']->name : 'Eure' }}" data-slug="{{ $deptIndex->has('27') ? $deptIndex['27']->slug : '' }}" />

        {{-- 28 - Eure-et-Loir --}}
        <path class="dept" d="M408,258 L426,244 L446,248 L462,242 L472,256 L468,274 L456,288 L440,292 L422,286 L406,292 L398,276 L400,264 Z"
              data-dept="{{ $deptIndex->has('28') ? '28' : '' }}" data-name="{{ $deptIndex->has('28') ? $deptIndex['28']->name : 'Eure-et-Loir' }}" data-slug="{{ $deptIndex->has('28') ? $deptIndex['28']->slug : '' }}" />

        {{-- 29 - Finistère --}}
        <path class="dept" d="M100,230 L122,218 L146,222 L166,216 L176,230 L170,248 L156,260 L138,264 L118,258 L100,264 L90,248 L92,236 Z"
              data-dept="{{ $deptIndex->has('29') ? '29' : '' }}" data-name="{{ $deptIndex->has('29') ? $deptIndex['29']->name : 'Finistère' }}" data-slug="{{ $deptIndex->has('29') ? $deptIndex['29']->slug : '' }}" />

        {{-- 30 - Gard --}}
        <path class="dept" d="M608,670 L626,658 L646,662 L662,654 L672,666 L670,684 L660,698 L644,704 L626,700 L610,706 L600,690 L602,676 Z"
              data-dept="{{ $deptIndex->has('30') ? '30' : '' }}" data-name="{{ $deptIndex->has('30') ? $deptIndex['30']->name : 'Gard' }}" data-slug="{{ $deptIndex->has('30') ? $deptIndex['30']->slug : '' }}" />

        {{-- 31 - Haute-Garonne --}}
        <path class="dept" d="M402,738 L420,724 L440,728 L456,720 L468,732 L466,750 L456,764 L440,770 L422,766 L406,772 L396,756 L396,744 Z"
              data-dept="{{ $deptIndex->has('31') ? '31' : '' }}" data-name="{{ $deptIndex->has('31') ? $deptIndex['31']->name : 'Haute-Garonne' }}" data-slug="{{ $deptIndex->has('31') ? $deptIndex['31']->slug : '' }}" />

        {{-- 32 - Gers --}}
        <path class="dept" d="M352,698 L372,684 L392,688 L408,682 L418,694 L416,712 L406,726 L390,732 L372,728 L356,734 L346,718 L346,706 Z"
              data-dept="{{ $deptIndex->has('32') ? '32' : '' }}" data-name="{{ $deptIndex->has('32') ? $deptIndex['32']->name : 'Gers' }}" data-slug="{{ $deptIndex->has('32') ? $deptIndex['32']->slug : '' }}" />

        {{-- 33 - Gironde --}}
        <path class="dept" d="M294,568 L316,554 L338,558 L358,550 L370,564 L366,584 L354,600 L336,606 L316,600 L296,606 L284,590 L286,574 Z"
              data-dept="{{ $deptIndex->has('33') ? '33' : '' }}" data-name="{{ $deptIndex->has('33') ? $deptIndex['33']->name : 'Gironde' }}" data-slug="{{ $deptIndex->has('33') ? $deptIndex['33']->slug : '' }}" />

        {{-- 34 - Hérault --}}
        <path class="dept" d="M544,718 L564,706 L584,710 L600,702 L612,714 L610,732 L600,746 L584,752 L564,748 L548,754 L538,738 L538,724 Z"
              data-dept="{{ $deptIndex->has('34') ? '34' : '' }}" data-name="{{ $deptIndex->has('34') ? $deptIndex['34']->name : 'Hérault' }}" data-slug="{{ $deptIndex->has('34') ? $deptIndex['34']->slug : '' }}" />

        {{-- 35 - Ille-et-Vilaine --}}
        <path class="dept" d="M222,246 L242,234 L262,238 L280,232 L290,244 L286,262 L274,274 L258,278 L240,274 L224,280 L214,264 L216,252 Z"
              data-dept="{{ $deptIndex->has('35') ? '35' : '' }}" data-name="{{ $deptIndex->has('35') ? $deptIndex['35']->name : 'Ille-et-Vilaine' }}" data-slug="{{ $deptIndex->has('35') ? $deptIndex['35']->slug : '' }}" />

        {{-- 36 - Indre --}}
        <path class="dept" d="M440,418 L458,404 L478,408 L494,400 L504,412 L502,430 L492,444 L476,450 L458,446 L442,452 L432,436 L434,424 Z"
              data-dept="{{ $deptIndex->has('36') ? '36' : '' }}" data-name="{{ $deptIndex->has('36') ? $deptIndex['36']->name : 'Indre' }}" data-slug="{{ $deptIndex->has('36') ? $deptIndex['36']->slug : '' }}" />

        {{-- 37 - Indre-et-Loire --}}
        <path class="dept" d="M386,374 L404,362 L424,366 L440,358 L450,370 L448,388 L438,402 L422,408 L404,404 L388,410 L378,394 L380,380 Z"
              data-dept="{{ $deptIndex->has('37') ? '37' : '' }}" data-name="{{ $deptIndex->has('37') ? $deptIndex['37']->name : 'Indre-et-Loire' }}" data-slug="{{ $deptIndex->has('37') ? $deptIndex['37']->slug : '' }}" />

        {{-- 38 - Isère --}}
        <path class="dept" d="M698,540 L716,526 L734,530 L750,522 L762,534 L760,552 L748,566 L732,572 L714,568 L698,574 L688,558 L690,546 Z"
              data-dept="{{ $deptIndex->has('38') ? '38' : '' }}" data-name="{{ $deptIndex->has('38') ? $deptIndex['38']->name : 'Isère' }}" data-slug="{{ $deptIndex->has('38') ? $deptIndex['38']->slug : '' }}" />

        {{-- 39 - Jura --}}
        <path class="dept" d="M680,396 L698,382 L716,386 L732,378 L742,392 L740,410 L730,424 L714,430 L696,426 L680,432 L672,416 L674,402 Z"
              data-dept="{{ $deptIndex->has('39') ? '39' : '' }}" data-name="{{ $deptIndex->has('39') ? $deptIndex['39']->name : 'Jura' }}" data-slug="{{ $deptIndex->has('39') ? $deptIndex['39']->slug : '' }}" />

        {{-- 40 - Landes --}}
        <path class="dept" d="M298,638 L318,624 L338,628 L356,620 L368,632 L366,650 L356,666 L338,672 L318,668 L300,674 L288,658 L290,644 Z"
              data-dept="{{ $deptIndex->has('40') ? '40' : '' }}" data-name="{{ $deptIndex->has('40') ? $deptIndex['40']->name : 'Landes' }}" data-slug="{{ $deptIndex->has('40') ? $deptIndex['40']->slug : '' }}" />

        {{-- 41 - Loir-et-Cher --}}
        <path class="dept" d="M428,330 L446,318 L466,322 L482,314 L492,326 L490,344 L480,358 L464,364 L446,360 L430,366 L420,350 L422,336 Z"
              data-dept="{{ $deptIndex->has('41') ? '41' : '' }}" data-name="{{ $deptIndex->has('41') ? $deptIndex['41']->name : 'Loir-et-Cher' }}" data-slug="{{ $deptIndex->has('41') ? $deptIndex['41']->slug : '' }}" />

        {{-- 42 - Loire --}}
        <path class="dept" d="M616,510 L634,496 L652,500 L666,494 L676,506 L674,522 L664,536 L648,542 L632,538 L616,544 L608,528 L610,516 Z"
              data-dept="{{ $deptIndex->has('42') ? '42' : '' }}" data-name="{{ $deptIndex->has('42') ? $deptIndex['42']->name : 'Loire' }}" data-slug="{{ $deptIndex->has('42') ? $deptIndex['42']->slug : '' }}" />

        {{-- 43 - Haute-Loire --}}
        <path class="dept" d="M570,560 L588,546 L606,550 L622,544 L632,556 L630,574 L620,588 L604,594 L588,590 L572,596 L562,580 L564,566 Z"
              data-dept="{{ $deptIndex->has('43') ? '43' : '' }}" data-name="{{ $deptIndex->has('43') ? $deptIndex['43']->name : 'Haute-Loire' }}" data-slug="{{ $deptIndex->has('43') ? $deptIndex['43']->slug : '' }}" />

        {{-- 44 - Loire-Atlantique --}}
        <path class="dept" d="M222,348 L242,334 L264,338 L282,330 L294,344 L290,364 L278,378 L260,384 L240,380 L222,386 L212,370 L214,354 Z"
              data-dept="{{ $deptIndex->has('44') ? '44' : '' }}" data-name="{{ $deptIndex->has('44') ? $deptIndex['44']->name : 'Loire-Atlantique' }}" data-slug="{{ $deptIndex->has('44') ? $deptIndex['44']->slug : '' }}" />

        {{-- 45 - Loiret --}}
        <path class="dept" d="M468,298 L488,284 L508,288 L526,280 L536,294 L532,314 L520,328 L504,334 L486,330 L468,336 L458,318 L462,304 Z"
              data-dept="{{ $deptIndex->has('45') ? '45' : '' }}" data-name="{{ $deptIndex->has('45') ? $deptIndex['45']->name : 'Loiret' }}" data-slug="{{ $deptIndex->has('45') ? $deptIndex['45']->slug : '' }}" />

        {{-- 46 - Lot --}}
        <path class="dept" d="M438,610 L458,596 L478,600 L494,594 L504,606 L502,624 L492,638 L476,644 L458,640 L442,646 L432,630 L434,616 Z"
              data-dept="{{ $deptIndex->has('46') ? '46' : '' }}" data-name="{{ $deptIndex->has('46') ? $deptIndex['46']->name : 'Lot' }}" data-slug="{{ $deptIndex->has('46') ? $deptIndex['46']->slug : '' }}" />

        {{-- 47 - Lot-et-Garonne --}}
        <path class="dept" d="M372,628 L392,614 L412,618 L428,612 L438,624 L436,642 L426,656 L410,662 L392,658 L376,664 L366,648 L366,634 Z"
              data-dept="{{ $deptIndex->has('47') ? '47' : '' }}" data-name="{{ $deptIndex->has('47') ? $deptIndex['47']->name : 'Lot-et-Garonne' }}" data-slug="{{ $deptIndex->has('47') ? $deptIndex['47']->slug : '' }}" />

        {{-- 48 - Lozère --}}
        <path class="dept" d="M564,618 L582,604 L600,608 L614,602 L624,614 L622,632 L612,646 L596,652 L580,648 L564,654 L556,638 L558,624 Z"
              data-dept="{{ $deptIndex->has('48') ? '48' : '' }}" data-name="{{ $deptIndex->has('48') ? $deptIndex['48']->name : 'Lozère' }}" data-slug="{{ $deptIndex->has('48') ? $deptIndex['48']->slug : '' }}" />

        {{-- 49 - Maine-et-Loire --}}
        <path class="dept" d="M296,358 L316,344 L338,348 L356,340 L368,354 L364,374 L352,388 L334,394 L314,390 L296,396 L286,378 L288,364 Z"
              data-dept="{{ $deptIndex->has('49') ? '49' : '' }}" data-name="{{ $deptIndex->has('49') ? $deptIndex['49']->name : 'Maine-et-Loire' }}" data-slug="{{ $deptIndex->has('49') ? $deptIndex['49']->slug : '' }}" />

        {{-- 50 - Manche --}}
        <path class="dept" d="M254,162 L272,148 L292,152 L308,146 L318,160 L316,178 L306,192 L290,198 L272,194 L256,200 L246,184 L248,170 Z"
              data-dept="{{ $deptIndex->has('50') ? '50' : '' }}" data-name="{{ $deptIndex->has('50') ? $deptIndex['50']->name : 'Manche' }}" data-slug="{{ $deptIndex->has('50') ? $deptIndex['50']->slug : '' }}" />

        {{-- 51 - Marne --}}
        <path class="dept" d="M548,172 L568,158 L590,162 L608,154 L620,168 L616,188 L604,202 L588,208 L568,204 L550,210 L540,194 L542,180 Z"
              data-dept="{{ $deptIndex->has('51') ? '51' : '' }}" data-name="{{ $deptIndex->has('51') ? $deptIndex['51']->name : 'Marne' }}" data-slug="{{ $deptIndex->has('51') ? $deptIndex['51']->slug : '' }}" />

        {{-- 52 - Haute-Marne --}}
        <path class="dept" d="M602,236 L620,222 L640,226 L656,218 L666,232 L664,250 L654,264 L638,270 L620,266 L604,272 L594,256 L596,242 Z"
              data-dept="{{ $deptIndex->has('52') ? '52' : '' }}" data-name="{{ $deptIndex->has('52') ? $deptIndex['52']->name : 'Haute-Marne' }}" data-slug="{{ $deptIndex->has('52') ? $deptIndex['52']->slug : '' }}" />

        {{-- 53 - Mayenne --}}
        <path class="dept" d="M290,290 L308,278 L328,282 L344,274 L354,286 L352,304 L342,318 L326,324 L308,320 L292,326 L282,310 L284,296 Z"
              data-dept="{{ $deptIndex->has('53') ? '53' : '' }}" data-name="{{ $deptIndex->has('53') ? $deptIndex['53']->name : 'Mayenne' }}" data-slug="{{ $deptIndex->has('53') ? $deptIndex['53']->slug : '' }}" />

        {{-- 54 - Meurthe-et-Moselle --}}
        <path class="dept" d="M670,178 L688,164 L708,168 L724,160 L734,174 L732,192 L722,206 L706,212 L688,208 L672,214 L662,198 L664,184 Z"
              data-dept="{{ $deptIndex->has('54') ? '54' : '' }}" data-name="{{ $deptIndex->has('54') ? $deptIndex['54']->name : 'Meurthe-et-Moselle' }}" data-slug="{{ $deptIndex->has('54') ? $deptIndex['54']->slug : '' }}" />

        {{-- 55 - Meuse --}}
        <path class="dept" d="M636,158 L654,144 L674,148 L690,140 L700,154 L698,172 L688,186 L672,192 L654,188 L638,194 L628,178 L630,164 Z"
              data-dept="{{ $deptIndex->has('55') ? '55' : '' }}" data-name="{{ $deptIndex->has('55') ? $deptIndex['55']->name : 'Meuse' }}" data-slug="{{ $deptIndex->has('55') ? $deptIndex['55']->slug : '' }}" />

        {{-- 56 - Morbihan --}}
        <path class="dept" d="M148,290 L168,278 L190,282 L208,274 L218,286 L214,304 L202,318 L186,324 L168,320 L150,326 L140,310 L142,296 Z"
              data-dept="{{ $deptIndex->has('56') ? '56' : '' }}" data-name="{{ $deptIndex->has('56') ? $deptIndex['56']->name : 'Morbihan' }}" data-slug="{{ $deptIndex->has('56') ? $deptIndex['56']->slug : '' }}" />

        {{-- 57 - Moselle --}}
        <path class="dept" d="M706,122 L724,108 L744,112 L762,104 L772,118 L770,138 L760,152 L744,158 L726,154 L708,160 L698,144 L700,130 Z"
              data-dept="{{ $deptIndex->has('57') ? '57' : '' }}" data-name="{{ $deptIndex->has('57') ? $deptIndex['57']->name : 'Moselle' }}" data-slug="{{ $deptIndex->has('57') ? $deptIndex['57']->slug : '' }}" />

        {{-- 58 - Nièvre --}}
        <path class="dept" d="M540,378 L560,364 L580,368 L596,360 L606,374 L604,392 L594,406 L578,412 L560,408 L542,414 L532,398 L534,384 Z"
              data-dept="{{ $deptIndex->has('58') ? '58' : '' }}" data-name="{{ $deptIndex->has('58') ? $deptIndex['58']->name : 'Nièvre' }}" data-slug="{{ $deptIndex->has('58') ? $deptIndex['58']->slug : '' }}" />

        {{-- 59 - Nord --}}
        <path class="dept" d="M530,60 L552,46 L576,50 L598,42 L610,56 L606,76 L594,90 L576,96 L554,92 L534,98 L522,82 L524,66 Z"
              data-dept="{{ $deptIndex->has('59') ? '59' : '' }}" data-name="{{ $deptIndex->has('59') ? $deptIndex['59']->name : 'Nord' }}" data-slug="{{ $deptIndex->has('59') ? $deptIndex['59']->slug : '' }}" />

        {{-- 60 - Oise --}}
        <path class="dept" d="M468,152 L486,140 L506,144 L522,136 L532,148 L530,164 L520,178 L504,184 L488,180 L472,186 L462,170 L464,158 Z"
              data-dept="{{ $deptIndex->has('60') ? '60' : '' }}" data-name="{{ $deptIndex->has('60') ? $deptIndex['60']->name : 'Oise' }}" data-slug="{{ $deptIndex->has('60') ? $deptIndex['60']->slug : '' }}" />

        {{-- 61 - Orne --}}
        <path class="dept" d="M334,236 L354,222 L376,226 L394,218 L404,232 L400,252 L388,266 L372,272 L352,268 L334,274 L324,256 L328,242 Z"
              data-dept="{{ $deptIndex->has('61') ? '61' : '' }}" data-name="{{ $deptIndex->has('61') ? $deptIndex['61']->name : 'Orne' }}" data-slug="{{ $deptIndex->has('61') ? $deptIndex['61']->slug : '' }}" />

        {{-- 62 - Pas-de-Calais --}}
        <path class="dept" d="M484,66 L504,52 L528,56 L548,48 L558,62 L554,82 L542,96 L524,102 L506,98 L488,104 L478,86 L480,72 Z"
              data-dept="{{ $deptIndex->has('62') ? '62' : '' }}" data-name="{{ $deptIndex->has('62') ? $deptIndex['62']->name : 'Pas-de-Calais' }}" data-slug="{{ $deptIndex->has('62') ? $deptIndex['62']->slug : '' }}" />

        {{-- 63 - Puy-de-Dôme --}}
        <path class="dept" d="M542,508 L562,494 L582,498 L598,490 L610,502 L608,520 L598,536 L582,542 L562,538 L544,544 L534,528 L536,514 Z"
              data-dept="{{ $deptIndex->has('63') ? '63' : '' }}" data-name="{{ $deptIndex->has('63') ? $deptIndex['63']->name : 'Puy-de-Dôme' }}" data-slug="{{ $deptIndex->has('63') ? $deptIndex['63']->slug : '' }}" />

        {{-- 64 - Pyrénées-Atlantiques --}}
        <path class="dept" d="M302,720 L322,706 L344,710 L362,702 L374,714 L372,732 L362,748 L344,754 L324,750 L306,756 L294,740 L296,726 Z"
              data-dept="{{ $deptIndex->has('64') ? '64' : '' }}" data-name="{{ $deptIndex->has('64') ? $deptIndex['64']->name : 'Pyrénées-Atlantiques' }}" data-slug="{{ $deptIndex->has('64') ? $deptIndex['64']->slug : '' }}" />

        {{-- 65 - Hautes-Pyrénées --}}
        <path class="dept" d="M344,754 L362,742 L380,746 L396,738 L406,750 L404,768 L394,782 L378,788 L360,784 L344,790 L336,774 L338,760 Z"
              data-dept="{{ $deptIndex->has('65') ? '65' : '' }}" data-name="{{ $deptIndex->has('65') ? $deptIndex['65']->name : 'Hautes-Pyrénées' }}" data-slug="{{ $deptIndex->has('65') ? $deptIndex['65']->slug : '' }}" />

        {{-- 66 - Pyrénées-Orientales --}}
        <path class="dept" d="M476,778 L494,766 L512,770 L528,762 L538,774 L536,792 L526,806 L510,812 L492,808 L476,814 L466,798 L470,784 Z"
              data-dept="{{ $deptIndex->has('66') ? '66' : '' }}" data-name="{{ $deptIndex->has('66') ? $deptIndex['66']->name : 'Pyrénées-Orientales' }}" data-slug="{{ $deptIndex->has('66') ? $deptIndex['66']->slug : '' }}" />

        {{-- 67 - Bas-Rhin --}}
        <path class="dept" d="M746,162 L764,148 L784,152 L800,144 L810,158 L808,178 L798,192 L782,198 L764,194 L748,200 L738,184 L740,170 Z"
              data-dept="{{ $deptIndex->has('67') ? '67' : '' }}" data-name="{{ $deptIndex->has('67') ? $deptIndex['67']->name : 'Bas-Rhin' }}" data-slug="{{ $deptIndex->has('67') ? $deptIndex['67']->slug : '' }}" />

        {{-- 68 - Haut-Rhin --}}
        <path class="dept" d="M756,216 L774,202 L792,206 L808,198 L818,212 L816,230 L806,244 L790,250 L772,246 L756,252 L748,236 L750,222 Z"
              data-dept="{{ $deptIndex->has('68') ? '68' : '' }}" data-name="{{ $deptIndex->has('68') ? $deptIndex['68']->name : 'Haut-Rhin' }}" data-slug="{{ $deptIndex->has('68') ? $deptIndex['68']->slug : '' }}" />

        {{-- 69 - Rhône --}}
        <path class="dept" d="M652,488 L668,476 L684,480 L696,474 L704,486 L702,500 L694,510 L680,514 L666,510 L654,516 L646,502 L648,492 Z"
              data-dept="{{ $deptIndex->has('69') ? '69' : '' }}" data-name="{{ $deptIndex->has('69') ? $deptIndex['69']->name : 'Rhône' }}" data-slug="{{ $deptIndex->has('69') ? $deptIndex['69']->slug : '' }}" />

        {{-- 70 - Haute-Saône --}}
        <path class="dept" d="M698,278 L718,264 L738,268 L754,260 L764,274 L762,292 L752,306 L736,312 L718,308 L702,314 L692,298 L694,284 Z"
              data-dept="{{ $deptIndex->has('70') ? '70' : '' }}" data-name="{{ $deptIndex->has('70') ? $deptIndex['70']->name : 'Haute-Saône' }}" data-slug="{{ $deptIndex->has('70') ? $deptIndex['70']->slug : '' }}" />

        {{-- 71 - Saône-et-Loire --}}
        <path class="dept" d="M616,408 L636,394 L658,398 L676,390 L688,404 L684,424 L672,440 L654,446 L634,442 L616,448 L606,430 L608,414 Z"
              data-dept="{{ $deptIndex->has('71') ? '71' : '' }}" data-name="{{ $deptIndex->has('71') ? $deptIndex['71']->name : 'Saône-et-Loire' }}" data-slug="{{ $deptIndex->has('71') ? $deptIndex['71']->slug : '' }}" />

        {{-- 72 - Sarthe --}}
        <path class="dept" d="M340,298 L360,284 L382,288 L398,280 L410,294 L406,314 L394,328 L376,334 L356,330 L340,336 L330,318 L332,304 Z"
              data-dept="{{ $deptIndex->has('72') ? '72' : '' }}" data-name="{{ $deptIndex->has('72') ? $deptIndex['72']->name : 'Sarthe' }}" data-slug="{{ $deptIndex->has('72') ? $deptIndex['72']->slug : '' }}" />

        {{-- 73 - Savoie --}}
        <path class="dept" d="M746,500 L764,486 L782,490 L798,482 L808,496 L806,514 L796,528 L780,534 L762,530 L746,536 L738,518 L740,506 Z"
              data-dept="{{ $deptIndex->has('73') ? '73' : '' }}" data-name="{{ $deptIndex->has('73') ? $deptIndex['73']->name : 'Savoie' }}" data-slug="{{ $deptIndex->has('73') ? $deptIndex['73']->slug : '' }}" />

        {{-- 74 - Haute-Savoie --}}
        <path class="dept" d="M746,454 L764,440 L782,444 L798,436 L808,450 L806,468 L796,482 L780,488 L762,484 L746,490 L738,474 L740,460 Z"
              data-dept="{{ $deptIndex->has('74') ? '74' : '' }}" data-name="{{ $deptIndex->has('74') ? $deptIndex['74']->name : 'Haute-Savoie' }}" data-slug="{{ $deptIndex->has('74') ? $deptIndex['74']->slug : '' }}" />

        {{-- 75 - Paris --}}
        <path class="dept" d="M474,206 L484,200 L494,204 L500,198 L506,206 L504,216 L498,222 L488,224 L480,220 L472,224 L468,214 Z"
              data-dept="{{ $deptIndex->has('75') ? '75' : '' }}" data-name="{{ $deptIndex->has('75') ? $deptIndex['75']->name : 'Paris' }}" data-slug="{{ $deptIndex->has('75') ? $deptIndex['75']->slug : '' }}" />

        {{-- 76 - Seine-Maritime --}}
        <path class="dept" d="M380,130 L400,116 L424,120 L444,112 L456,126 L452,146 L440,160 L422,166 L400,162 L382,168 L370,150 L374,136 Z"
              data-dept="{{ $deptIndex->has('76') ? '76' : '' }}" data-name="{{ $deptIndex->has('76') ? $deptIndex['76']->name : 'Seine-Maritime' }}" data-slug="{{ $deptIndex->has('76') ? $deptIndex['76']->slug : '' }}" />

        {{-- 77 - Seine-et-Marne --}}
        <path class="dept" d="M500,210 L520,196 L542,200 L560,192 L570,206 L566,226 L554,240 L536,246 L516,242 L500,248 L490,232 L494,218 Z"
              data-dept="{{ $deptIndex->has('77') ? '77' : '' }}" data-name="{{ $deptIndex->has('77') ? $deptIndex['77']->name : 'Seine-et-Marne' }}" data-slug="{{ $deptIndex->has('77') ? $deptIndex['77']->slug : '' }}" />

        {{-- 78 - Yvelines --}}
        <path class="dept" d="M438,204 L454,192 L468,196 L478,190 L484,200 L482,212 L476,220 L464,224 L452,220 L440,226 L432,216 L434,208 Z"
              data-dept="{{ $deptIndex->has('78') ? '78' : '' }}" data-name="{{ $deptIndex->has('78') ? $deptIndex['78']->name : 'Yvelines' }}" data-slug="{{ $deptIndex->has('78') ? $deptIndex['78']->slug : '' }}" />

        {{-- 79 - Deux-Sèvres --}}
        <path class="dept" d="M324,428 L342,414 L362,418 L378,410 L388,424 L386,442 L376,456 L360,462 L342,458 L326,464 L316,448 L318,434 Z"
              data-dept="{{ $deptIndex->has('79') ? '79' : '' }}" data-name="{{ $deptIndex->has('79') ? $deptIndex['79']->name : 'Deux-Sèvres' }}" data-slug="{{ $deptIndex->has('79') ? $deptIndex['79']->slug : '' }}" />

        {{-- 80 - Somme --}}
        <path class="dept" d="M460,96 L480,82 L504,86 L524,78 L534,92 L530,112 L518,126 L500,132 L480,128 L462,134 L452,118 L454,104 Z"
              data-dept="{{ $deptIndex->has('80') ? '80' : '' }}" data-name="{{ $deptIndex->has('80') ? $deptIndex['80']->name : 'Somme' }}" data-slug="{{ $deptIndex->has('80') ? $deptIndex['80']->slug : '' }}" />

        {{-- 81 - Tarn --}}
        <path class="dept" d="M468,688 L488,674 L508,678 L524,670 L534,682 L532,700 L522,714 L506,720 L488,716 L472,722 L462,706 L462,694 Z"
              data-dept="{{ $deptIndex->has('81') ? '81' : '' }}" data-name="{{ $deptIndex->has('81') ? $deptIndex['81']->name : 'Tarn' }}" data-slug="{{ $deptIndex->has('81') ? $deptIndex['81']->slug : '' }}" />

        {{-- 82 - Tarn-et-Garonne --}}
        <path class="dept" d="M426,666 L444,652 L462,656 L478,648 L488,660 L486,678 L476,692 L460,698 L444,694 L428,700 L418,684 L420,672 Z"
              data-dept="{{ $deptIndex->has('82') ? '82' : '' }}" data-name="{{ $deptIndex->has('82') ? $deptIndex['82']->name : 'Tarn-et-Garonne' }}" data-slug="{{ $deptIndex->has('82') ? $deptIndex['82']->slug : '' }}" />

        {{-- 83 - Var --}}
        <path class="dept" d="M754,700 L774,688 L794,692 L812,684 L824,696 L822,714 L812,728 L794,734 L774,730 L756,736 L746,718 L748,706 Z"
              data-dept="{{ $deptIndex->has('83') ? '83' : '' }}" data-name="{{ $deptIndex->has('83') ? $deptIndex['83']->name : 'Var' }}" data-slug="{{ $deptIndex->has('83') ? $deptIndex['83']->slug : '' }}" />

        {{-- 84 - Vaucluse --}}
        <path class="dept" d="M680,648 L696,636 L712,640 L726,632 L734,644 L732,660 L724,672 L710,676 L696,672 L682,678 L674,664 L676,654 Z"
              data-dept="{{ $deptIndex->has('84') ? '84' : '' }}" data-name="{{ $deptIndex->has('84') ? $deptIndex['84']->name : 'Vaucluse' }}" data-slug="{{ $deptIndex->has('84') ? $deptIndex['84']->slug : '' }}" />

        {{-- 85 - Vendée --}}
        <path class="dept" d="M252,420 L272,406 L294,410 L312,402 L324,416 L320,436 L308,450 L290,456 L270,452 L252,458 L242,440 L246,426 Z"
              data-dept="{{ $deptIndex->has('85') ? '85' : '' }}" data-name="{{ $deptIndex->has('85') ? $deptIndex['85']->name : 'Vendée' }}" data-slug="{{ $deptIndex->has('85') ? $deptIndex['85']->slug : '' }}" />

        {{-- 86 - Vienne --}}
        <path class="dept" d="M370,438 L388,424 L408,428 L424,420 L434,434 L432,452 L422,466 L406,472 L388,468 L372,474 L362,458 L364,444 Z"
              data-dept="{{ $deptIndex->has('86') ? '86' : '' }}" data-name="{{ $deptIndex->has('86') ? $deptIndex['86']->name : 'Vienne' }}" data-slug="{{ $deptIndex->has('86') ? $deptIndex['86']->slug : '' }}" />

        {{-- 87 - Haute-Vienne --}}
        <path class="dept" d="M402,490 L420,476 L440,480 L456,472 L466,486 L464,504 L454,518 L438,524 L420,520 L404,526 L394,510 L396,496 Z"
              data-dept="{{ $deptIndex->has('87') ? '87' : '' }}" data-name="{{ $deptIndex->has('87') ? $deptIndex['87']->name : 'Haute-Vienne' }}" data-slug="{{ $deptIndex->has('87') ? $deptIndex['87']->slug : '' }}" />

        {{-- 88 - Vosges --}}
        <path class="dept" d="M714,216 L732,202 L752,206 L768,198 L778,212 L776,230 L766,244 L750,250 L732,246 L716,252 L706,236 L708,222 Z"
              data-dept="{{ $deptIndex->has('88') ? '88' : '' }}" data-name="{{ $deptIndex->has('88') ? $deptIndex['88']->name : 'Vosges' }}" data-slug="{{ $deptIndex->has('88') ? $deptIndex['88']->slug : '' }}" />

        {{-- 89 - Yonne --}}
        <path class="dept" d="M538,288 L556,274 L576,278 L594,270 L604,284 L600,304 L588,318 L572,324 L554,320 L536,326 L526,308 L530,294 Z"
              data-dept="{{ $deptIndex->has('89') ? '89' : '' }}" data-name="{{ $deptIndex->has('89') ? $deptIndex['89']->name : 'Yonne' }}" data-slug="{{ $deptIndex->has('89') ? $deptIndex['89']->slug : '' }}" />

        {{-- 90 - Territoire de Belfort --}}
        <path class="dept" d="M756,250 L766,244 L776,248 L784,242 L790,252 L788,262 L782,268 L774,270 L766,268 L758,272 L752,262 Z"
              data-dept="{{ $deptIndex->has('90') ? '90' : '' }}" data-name="{{ $deptIndex->has('90') ? $deptIndex['90']->name : 'Territoire de Belfort' }}" data-slug="{{ $deptIndex->has('90') ? $deptIndex['90']->slug : '' }}" />

        {{-- 91 - Essonne --}}
        <path class="dept" d="M468,232 L482,224 L496,228 L506,222 L512,232 L510,244 L504,252 L492,256 L480,252 L468,256 L462,244 Z"
              data-dept="{{ $deptIndex->has('91') ? '91' : '' }}" data-name="{{ $deptIndex->has('91') ? $deptIndex['91']->name : 'Essonne' }}" data-slug="{{ $deptIndex->has('91') ? $deptIndex['91']->slug : '' }}" />

        {{-- 92 - Hauts-de-Seine --}}
        <path class="dept" d="M466,204 L474,198 L482,202 L486,196 L492,202 L490,210 L486,216 L478,218 L472,216 L466,220 L462,212 Z"
              data-dept="{{ $deptIndex->has('92') ? '92' : '' }}" data-name="{{ $deptIndex->has('92') ? $deptIndex['92']->name : 'Hauts-de-Seine' }}" data-slug="{{ $deptIndex->has('92') ? $deptIndex['92']->slug : '' }}" />

        {{-- 93 - Seine-Saint-Denis --}}
        <path class="dept" d="M488,196 L498,190 L506,194 L512,188 L518,196 L516,206 L510,212 L502,214 L496,210 L488,214 L484,204 Z"
              data-dept="{{ $deptIndex->has('93') ? '93' : '' }}" data-name="{{ $deptIndex->has('93') ? $deptIndex['93']->name : 'Seine-Saint-Denis' }}" data-slug="{{ $deptIndex->has('93') ? $deptIndex['93']->slug : '' }}" />

        {{-- 94 - Val-de-Marne --}}
        <path class="dept" d="M484,216 L494,210 L504,214 L510,208 L516,216 L514,226 L508,232 L500,234 L492,230 L484,234 L480,224 Z"
              data-dept="{{ $deptIndex->has('94') ? '94' : '' }}" data-name="{{ $deptIndex->has('94') ? $deptIndex['94']->name : 'Val-de-Marne' }}" data-slug="{{ $deptIndex->has('94') ? $deptIndex['94']->slug : '' }}" />

        {{-- 95 - Val-d'Oise --}}
        <path class="dept" d="M458,186 L470,178 L482,182 L492,176 L498,186 L496,198 L488,206 L478,208 L468,204 L458,210 L452,198 Z"
              data-dept="{{ $deptIndex->has('95') ? '95' : '' }}" data-name="{{ $deptIndex->has('95') ? $deptIndex['95']->name : &quot;Val-d'Oise&quot; }}" data-slug="{{ $deptIndex->has('95') ? $deptIndex['95']->slug : '' }}" />
    </svg>

    {{-- DOM-TOM --}}
    <div class="mt-6">
        <h3 class="text-sm font-semibold text-gray-500 text-center mb-3">Outre-mer</h3>
        <div class="flex flex-wrap justify-center gap-3">
            @php
                $domtom = [
                    '971' => 'Guadeloupe',
                    '972' => 'Martinique',
                    '973' => 'Guyane',
                    '974' => 'La Réunion',
                    '976' => 'Mayotte',
                ];
            @endphp
            @foreach ($domtom as $number => $defaultName)
                @php
                    $dept = $deptIndex->get($number);
                @endphp
                <a href="{{ $dept ? '/' . $dept->slug : '#' }}"
                   class="flex items-center justify-center w-28 h-16 rounded-lg border-2 border-blue-200 bg-blue-50 text-blue-900 text-xs font-semibold text-center leading-tight hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-colors duration-150"
                   data-dept="{{ $number }}"
                   data-name="{{ $dept ? $dept->name : $defaultName }}"
                   data-slug="{{ $dept ? $dept->slug : '' }}">
                    <div>
                        <div class="text-[10px] opacity-70">{{ $number }}</div>
                        <div>{{ $dept ? $dept->name : $defaultName }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
