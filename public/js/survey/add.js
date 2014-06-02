$(document).ready(function() {
    
    var rTable, pTable, 
        surveyForm = $('#createSurveyForm'),
        submitBtn = $('#submitSurvey');
    
    submitBtn.on('click', function(event){
        submitHandler();
    });
    
    $("#participants tbody tr").click( function( e ) {
        if ( $(this).hasClass('row_selected') ) {
            $(this).removeClass('row_selected');
            $(this).find('.rowSelect').prop('checked', false);
        }
        else{
            $(this).addClass('row_selected');
            $(this).find('.rowSelect').prop('checked', true);
        }
    });
    
    $("#recordings tbody tr").click( function( e ) {
        $('input[name=recordingId]:checked', '#recordings').prop('checked', false);
        if ( $(this).hasClass('row_selected') ) {
            $(this).removeClass('row_selected');
        }
        else{
            rTable.$('tr.row_selected').removeClass('row_selected');
            $(this).addClass('row_selected');
            $(this).find('.rowSelect').prop('checked', true);
        }
    });
    
    pTable = $('#participants').dataTable({
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 0 ] }
        ]
    });
    
    rTable = $('#recordings').dataTable({
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 0 ] }
        ]
    });
    
    function submitHandler(){
        var survey = surveyForm.serializeObject();
        
        survey.participants = [];
        pTable.$('tr.row_selected').each(function(i, el){
            survey.participants[i] = $(el).attr('id');
        });
        
        survey.recording = rTable.$('tr.row_selected').attr('id');
        
        $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/addHandler',
                data: JSON.stringify(survey), 
                success:function(data){
                    window.location.replace("/survey/view/"+data.id);
                },
                error:function(){
                
                }
        });
    }
    
    $.fn.serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
});
 
function fnGetSelected( localTable )
{
    return localTable.$('tr.row_selected');
}