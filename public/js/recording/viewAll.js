var mTable;

$(document).ready(function() {
    
    mTable = $('#recordings').dataTable({ 
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 4 ] }
        ]
    });

});
