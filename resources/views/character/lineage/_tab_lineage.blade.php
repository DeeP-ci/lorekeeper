<style type="text/css">
    .lineage-tree {
        margin: 0;
        padding: 0;
        margin-right: -15px;
    }

    @media (max-width: 767px) {
        .lineage-tree {
            margin-left: -5px;
        }
    }

    .lineage-tree .ancestor {
        margin-left: 15px;
        margin-right: 15px;
        text-align: center;
        width: 100%;
    }

    .grandparent-lineage, .lineage-greatgrandparents .ancestor {
        position: relative;
    }

    .parent-lineage {
        width: 100%;
        position: relative;
    }

    .lineage-parent {
        width: 33.4%;
    }

    .lineage-grandparents {
        width: 66.6%;
    }

    .lineage-grandparent, .lineage-greatgrandparents {
        width: 50%;
    }

    .lineage-greatgrandparent {
        position: relative;
    }

    .lineage-tree-branch::before,
    .lineage-tree-branch::after,
    .lineage-tree .branch-connector::after {
        position: absolute;
        left: 0;
        width: 15px;
        display: block;
        content: '';
        border: 0px solid black;
    }

    .lineage-tree-branch::after {
        top: 50%;
        border-top-width: 1px;
    }

    .lineage-tree .branch-connector {
        padding-left: 0;
        padding-right: 0;
        position: relative;
    }

    .lineage-tree .branch-connector::after {
        left: -15px;
        top: 50%;
        border-bottom-width: 1px;
    }

    .lineage-tree-branch.ancestor::before,
    .lineage-tree-branch.ancestor::after {
        left: -15px;
    }

    .lineage-tree-branch.branch-start::before,
    .lineage-tree-branch.branch-end::before,
    .lineage-tree-branch.branch-middle::before {
        bottom: 0;
        top: 0;
        border-left-width: 1px;
    }

    .lineage-tree-branch.branch-start::before {
        top: 50%;
    }

    .lineage-tree-branch.branch-end::before {
        bottom: 50%;
    }
</style>

@if($lineage != null && count($lineage->getParents()) > 0)
    <div class="lineage-tree"> <!-- contains full lineage -->
        <?php $i=0; $parents = $lineage->getParents(); ?>
        @foreach($parents as $parent)
            <div class="d-flex parent-lineage">
                <div class="d-flex align-items-center lineage-parent lineage-tree-branch {{ count($parents) > 1 ? ($i == 0 ? 'branch-start' : ($i == count($parents)-1 ? 'branch-end' : 'branch-middle')) : '' }}">
                    <div class="ancestor">{!! $parent->parent->display_name !!}</div>
                </div>
                <div class="d-flex-vertical align-items-stretch lineage-grandparents {{ count($parent->parent->parents) > 0 ? 'branch-connector' : ''}}">
                <?php $j=0; $gps = $parent->parent->getParents(); ?>
                @foreach($gps as $gp_link)
                    <div class="d-flex grandparent-lineage">
                            <div class="d-flex align-items-center lineage-grandparent lineage-tree-branch {{ count($gps) > 1 ? ($j == 0 ? 'branch-start' : ($j == count($gps)-1 ? 'branch-end' : 'branch-middle')) : '' }}">
                                <div class="ancestor">{!! $gp_link->parent->display_name !!}</div>
                            </div>
                            <div class="lineage-greatgrandparents {{ count($gp_link->parent->parents) > 0 ? 'branch-connector' : '' }}">
                            <?php $k=0; $ggp = $gp_link->parent->getParents(); ?>
                            @foreach($ggp as $ggp_link)
                                <div class="d-flex lineage-greatgrandparent align-items-center lineage-tree-branch {{ count($ggp) > 1 ? ($k == 0 ? 'branch-start' : ($k == count($ggp)-1 ? 'branch-end' : 'branch-middle')) : '' }}">
                                    <div class="ancestor">{!! $ggp_link->parent->display_name !!}</div>
                                </div>
                                <?php $k++; ?>
                            @endforeach
                            </div>
                        </div>
                    <?php $j++; ?>
                @endforeach
                </div>
            </div>
            <?php $i++; ?>
        @endforeach
    </div>
@else
    <p class="mb-0">Ancestry unknown.</p>
@endif