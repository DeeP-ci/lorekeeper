<script>
    $(document).ready(function() {

        $('#parentList').find('.lineage-data').selectize();

        var $characterSelect = $('#lineageHelperData').find('.character-select');
        var $rogueSelect = $('#lineageHelperData').find('.rogue-select');
        var $newRogue = $('#lineageHelperData').find('.rogue-new');

        $('#add-parent').on('click', function(e) {
            e.preventDefault();
            addParentRow();
        });
        $('.remove-parent').on('click', function(e) {
            e.preventDefault();
            removeParentRow($(this));
        })
        function addParentRow() {
            var $clone = $('.parent-row').clone();
            $('#parentList').append($clone);
            $clone.removeClass('hide parent-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-parent').on('click', function(e) {
                e.preventDefault();
                removeParentRow($(this));
            })
            $clone.find('.lineage-data').selectize();
            attachLineageTypeListener($clone.find('.lineage-type-select'));
        }
        function removeParentRow($trigger) {
            $trigger.parent().parent().remove();
        }
        function attachLineageTypeListener($node) {
            $node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.lineage-data-select');

                var $clone = null; var flag = true;
                if(val == 'Character') $clone = $characterSelect.clone();
                else if (val == 'Rogue') $clone = $rogueSelect.clone();
                else if (val == 'New') $clone = $newRogue.clone();
                else flag = false;

                $cell.html('');
                $cell.append($clone);
                if (flag && val != "New") $clone.selectize();
            });
        }
    });
</script>