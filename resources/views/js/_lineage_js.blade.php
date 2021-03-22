<script>
    $(document).ready(function() {

        $(document).find('.lineage-owner-data').selectize();
        $('#parentList').find('.lineage-data').selectize();
        $('#childList').find('.lineage-data').selectize();

        $('#add-parent').on('click', function(e) {
            e.preventDefault();
            addNewRow('parent');
        });
        $('#add-child').on('click', function(e) {
            e.preventDefault();
            addNewRow('child');
        });
        $('.remove-parent, .remove-child').on('click', function(e) {
            e.preventDefault();
            removeParentRow($(this));
        });

        function addNewRow(type) {
            var $clone = $('.'+ type +'-row').clone();
            $('#'+ type +'List').append($clone);
            $clone.removeClass('hide '+ type +'-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-'+ type).on('click', function(e) {
                e.preventDefault();
                removeParentRow($(this));
            })
            $clone.find('.lineage-data').selectize();
            attachLineageTypeListener($clone.find('.lineage-type-select'), type);
        }

        function removeParentRow($trigger) {
            $trigger.parent().parent().remove();
        }

        function attachLineageTypeListener($node, type) {
            $node.on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.lineage-data-select');

                var $clone = null; var flag = true;
                if(val == 'Character')
                    $clone = $('#lineageHelperData').find('.'+ type +'-data.character-select').clone();
                else if (val == 'Rogue')
                    $clone = $('#lineageHelperData').find('.'+ type +'-data.rogue-select').clone();
                else if (val == 'New')
                    $clone = $('#lineageHelperData').find('.'+ type +'-data.rogue-new').clone();
                else flag = false;

                $cell.html('');
                $cell.append($clone);
                if (flag && val != "New") $clone.selectize();
            });
        }
    });
</script>