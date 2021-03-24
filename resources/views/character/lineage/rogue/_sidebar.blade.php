<ul>
    <li class="sidebar-header"><a href="{{ $lineage->url }}" class="card-link">{{ $lineage->name }}</a></li>
    <li class="sidebar-section"> {{-- Lineage Extension Links --}}
        <div class="sidebar-section-header">Lineage</div>
        <div class="sidebar-item"><a href="{{ $lineage->url . '/ancestors'}}" class="{{ set_active('rogue/'.$lineage->id.'/ancestors') }}">Ancestors</a></div>
        <div class="sidebar-item"><a href="{{ $lineage->url . '/offspring'}}" class="{{ set_active('rogue/'.$lineage->id.'/offspring') }}">Offspring</a></div>
        <div class="sidebar-item"><a href="{{ $lineage->url . '/relatives'}}" class="{{ set_active('rogue/'.$lineage->id.'/relatives') }}">Relatives</a></div>
    </li>
    @if(Auth::check() && Auth::user()->hasPower('manage_masterlist'))
        <li class="sidebar-section">
            <div class="sidebar-section-header">Manage</div>
            <div class="sidebar-item"><a href="{{ 'admin/masterlist/edit/'. $lineage->id }}">ACP Edit</a></div>
        </li>
    @endif
</ul>