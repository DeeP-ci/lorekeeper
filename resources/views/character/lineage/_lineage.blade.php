@if($lineageType == 'ancestors' || $lineageType == 'descendants' || $lineageType == 'relatives')
    {{-- Grabs the overview pages for Ancestors, Descendants and Cousins --}}
    @include('character.lineage._'.$lineageType, ['lineage' => $lineage])
@else
    {{-- Grabs the page that shows all the specified relatives --}}
    <?php
        $parent = ('parents' == substr($lineageType, strlen($lineageType) - 7, strlen($lineageType)));
        $relatives = null;
        if ($character->lineage != null) {
            switch ($lineageType) {
                case 'parents':
                    $relatives = $lineage->getParents();
                    break;
                case 'grandparents':
                    $relatives = $lineage->getGrandparents();
                    break;
                case 'great-grandparents':
                    $relatives = $lineage->getGreatGrandparents();
                    break;
                case 'children':
                    $relatives = $lineage->getChildren();
                    break;
                case 'grandchildren':
                    $relatives = $lineage->getGrandchildren();
                    break;
                case 'great-grandchildren':
                    $relatives = $lineage->getGreatGrandchildren();
                    break;
                case 'siblings':
                    $relatives = $lineage->getSiblings();
                    break;
                case 'niblings':
                    $relatives = $lineage->getNiblings();
                    break;
                case 'aunts-uncles':
                    $relatives = $lineage->getAuntsUncles();
                    break;
                case 'cousins':
                    $relatives = $lineage->getCousins();
                    break;
            }
            if ($relatives != null && $lineageType != 'parents' && $lineageType != 'children') $relatives = $relatives->get();
        }
    ?>
    @include('character.lineage._listing', [
        'pageTitle' => $pageTitle,
        'lineageType' => $lineageType,
        'character' => $character,
        'lineage' => $character->lineage,
        'parent' => $parent,
        'relatives' => $relatives,
    ])
@endif