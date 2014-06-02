$(document).ready(function(){
    $('#surveyVideos').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": false,
                     "bLengthChange": false,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 2},
        {column_number: 3},
        {column_number: 4},
        {column_number: 5},
    ]
    );
    
    $('#removedVideos').dataTable(
            {
                     "bSort": true,
                     "bPaginate": true,
                     "bFilter": true,
                     "bJQueryUI": true,
                     "bAutoWidth": false,
                     "bLengthChange": false,
                     "bSortable": false,
                     "bRender": true,
                     "bDestroy": true,
                     //"bProcessing": true,
                     //"bServerSide": true,
                     //"sAjaxsource": 'projectMember/removeHandler',
            }
    ).yadcf([
        {column_number: 1},
        {column_number: 2},
        {column_number: 3},
        {column_number: 4},
    ]
    );

    var editstatus = 0; 
    var editedRows = {};
    function update()
    {
        var editId = this.id;
        if(editstatus === 0)
        {
            // go through each column in the table
            $('#surveyVideos td').each(function() {
                if($(this).attr('id') == editId)
                {
                        // get column text
                        var colData = $(this).text();
                        // get column class
                        var colClass = $(this).attr('class');
                        // get column id
                        var colId = $(this).attr('id');
                        // create input element                
                        var input = $('<td><input/></td>');
                        // fill it with data
                        input.find('input').val(colData).attr('class', colClass).attr('name', colClass).attr('id', colId);
                        // now. replace
                        $(this).replaceWith(input);
                } else{}
            });
                editstatus = 1;
        }
        else
        {
                var id = $('tbody').find('input').attr('id'),
                    name = $('tbody').find('input.name').val(),
                    link = $('tbody').find('input.link').val(),
                    offsetTime = $('tbody').find('input.offsetTime').val(),
                    position = $('tbody').find('input.position').val();
            
                $.ajax({
                dataType: 'json',
                        type: 'POST',
                        url: '/survey/videosEditHandler',
                        data: {
                                id: id,
                                name: name,
                                link: link,
                                offsetTime: offsetTime,
                                position: position
                        },
                        success:function(data){
                        if (data.message == "success")
                        {
                        }
                        else
                        {
                        }
                        },
                        error:function(){
                        // failed request; give feedback to user
                        // alert('Unsuccessful return!');
                        }
                });
                
                // go through each input in the table
                $('tbody').find('input').each(function() {
                var colData = $('td').find('input').val();
                // get column class
                var colClass = $('td').find('input').attr('class');
                // get column id
                var colId = $('td').find('input').attr('id');
                // create td element
                var col = $('<td></td>');
                // fill it with data
                col.addClass(colClass).text(colData).attr('id', colId);
                // now. replace
                $(this).replaceWith(col);
                 });
                editstatus = 0;
        }
    }
    $('.editpts').live('click', update);      
   
    $('a.remove').live('click', function(){
            var removeId = this.id;
            var video = $('#' + this.id + '.name').text();
            var message = 'Are you sure you want to remove video ' + video + ' ?';
            $('#confirmRemove').html(message).data('id', removeId).dialog('open');    
            return false;
    });
    
    $('#confirmRemove').dialog({
        autoOpen: false,
        title: 'Remove Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var removeId = $(this).data('id');
                var value = $('input.hidden').val(removeId);
              
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/videosRemoveHandler',
                data: value, 
                
                success:function(data){
                    if(data.message == "success")
                    { 
                        var active = $('#surveyVideos').dataTable();
                        var removed = $('#removedVideos').dataTable();
                        var rowToRemove = $('#surveyVideos').find('tr#' + data.id);
                        active.fnDeleteRow(rowToRemove[0]);          
                        
                        removed.fnAddData([
                           '<a href="#" id="' + data.id + '" class="reinstate">Reinstate</a></td>', 
                           data.name,
                           data.link,
                           data.offsetTime,
                           data.position
                         ]
                         );
                         $('#removedVideos tr:last').attr('id', data.id);
                         $('#removedVideos tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#removedVideos tr:last').children('td:nth-child(2)').attr('class', 'name').attr('id', data.id);
                         $('#removedVideos tr:last').children('td:nth-child(3)').attr('class', 'link').attr('id', data.id);
                         $('#removedVideos tr:last').children('td:nth-child(4)').attr('class', 'offsetTime').attr('id', data.id);
                         $('#removedVideos tr:last').children('td:nth-child(5)').attr('class', 'position').attr('id', data.id);
                         active.fnFilterOnReturn();
                         removed.fnFilterOnReturn();
                        /*
                           '<td class="changers"><a href="#" id="' + data.id + '" class="reinstate">Reinstate</a></td>', 
                           '<td id="' + data.id + '" class="userName">' + data.userName + '</td>',
                           '<td id="' + data.id + '" class="instrument">' + data.instrument + '</td>',
                           '<td id="' + data.id + '" class="playerNumber">' + data.playerNumber + '</td>',
                           '<td id="' + data.id + '" class="desk">' + data.desk + '</td>',
                           '<td id="' + data.id + '" class="playingPart">' + data.playingPart + '</td>' 
                        */
                        active.fnDraw();
                        removed.fnDraw();
                    }
                    else
                    {
                    }
                },
                error:function(){
                // failed request; give feedback to user
                   // alert('Unsuccessful return!');
                }
                });
                
            },
            No: function() {
                $(this).dialog("close");
            }
        }
    });
    
    $('a.reinstate').live('click', function(){
            var reinstateId = this.id;
            var video = $('#' + this.id + '.name').text();
            var message = 'Are you sure you want to reinstate video ' + video + ' ?';
            $('#confirmReinstate').html(message).data('id', reinstateId).dialog('open');    
            return false;
    });
    
    $('#confirmReinstate').dialog({
        autoOpen: false,
        title: 'Reinstate Confirmation',
        width: 'auto',
        resizable: false,
        modal: true,
        buttons: {
            Yes: function() {
                $(this).dialog("close");
                var reinstateId = $(this).data('id');
                var value = $('input.hidden').val(reinstateId);
             
                $.ajax({
                dataType: 'json',
                type: 'POST',
                url: '/survey/videosReinstateHandler',
                data: value, 

                success:function(data){
                    if(data.message == "success")
                    {
                         var active = $('#surveyVideos').dataTable();
                         var removed = $('#removedVideos').dataTable();
                         var rowToReinstate = $('#removedVideos').find('tr#' + data.id);                
                         removed.fnDeleteRow(rowToReinstate[0]);
                         
                         active.fnAddData([
                           '<a href="#" id="' + data.id + '" class="remove">Remove</a></td>', 
                           '<a href="#" id="' + data.id + '" class="editpts">Edit</a></td>', 
                           data.name,
                           data.link,
                           data.offsetTime,
                           data.position
                         ]
                         );
  
                         $('#surveyVideos tr:last').attr('id', data.id);
                         $('#surveyVideos tr:last').children('td:nth-child(1)').attr('class', 'changers');
                         $('#surveyVideos tr:last').children('td:nth-child(2)').attr('class', 'changers');
                         $('#surveyVideos tr:last').children('td:nth-child(3)').attr('class', 'name').attr('id', data.id);
                         $('#surveyVideos tr:last').children('td:nth-child(4)').attr('class', 'link').attr('id', data.id);
                         $('#surveyVideos tr:last').children('td:nth-child(5)').attr('class', 'offsetTime').attr('id', data.id);
                         $('#surveyVideos tr:last').children('td:nth-child(6)').attr('class', 'position').attr('id', data.id);
                         active.fnFilterOnReturn();
                         removed.fnFilterOnReturn();
                            /*
                           '<tr id="' + data.id + '">'+       
                           '<td class="changers"><a href="#" id="' + data.id + '" class="remove">Remove</a></td>', 
                           '<td class="changers"><a href="#" id="' + data.id + '" class="editpts">Edit</a></td>', 
                           '<td id="' + data.id + '" class="userName">' + data.userName + '</td>',
                           '<td id="' + data.id + '" class="instrument">' + data.instrument + '</td>',
                           '<td id="' + data.id + '" class="playerNumber">' + data.playerNumber + '</td>',
                           '<td id="' + data.id + '" class="desk">' + data.desk + '</td>',
                           '<td id="' + data.id + '" class="playingPart">' + data.playingPart + '</td>' + 
                           '</tr>'
                           */
                         active.fnDraw();
                         removed.fnDraw();
                        
                    }
                    else
                    {
                    }
                },
                error:function(){
                // failed request; give feedback to user
                   // alert('Unsuccessful return!');
                }
                });
                
            },
            No: function() {
                $(this).dialog("close");
            }
        }
    });
});
 
