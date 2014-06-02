$(document).ready(function(){

function JSON2PSV(objArray) {
        var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;

        var str = '';
        var line = '';
        
        /*vertical column format */
        /*
        var head = array[0];
        for (var index in array[0]) {
                var value = array[0][index]
                if(typeof value != 'string')
                {
                     value = JSON.stringify(value);
                }
                line += index + ' | ' + value + '\n';
        }
        line = line.slice(0, -1);
        str += line + '\r';
        */
        /* The horizontal column stacked format */
        var head = array[0];
        for (var index in array[0]) {
                var value = index + "";
                line += '' + value.replace(/"/g, '') + '\t';
        }
  

        line = line.slice(0, -1);
        str += line + '\r\n';
    
    
        for (var i = 0; i < array.length; i++) {
        var line = '';

            for (var index in array[i]) {
                var value = array[0][index];
                if(typeof value != 'string')
                {
                     value = JSON.stringify(value);
                }
                line += '' + value.replace(/"/g, '') + '\t';
            }
 

        line = line.slice(0, -1);
        str += line + '\r\n';
    }
        
        return str;
}

function download(strData, strFileName, strMimeType) {
    var D = document,
        a = D.createElement("a");
        strMimeType= strMimeType || "application/octet-stream";


    if (navigator.msSaveBlob) { 
        return navigator.msSaveBlob(new Blob([strData], {type: strMimeType}), strFileName);
    } 

    if ('download' in a) { 
        a.href = "data:" + strMimeType + "," + encodeURIComponent(strData);
        a.setAttribute("download", strFileName);
        a.innerHTML = "downloading...";
        D.body.appendChild(a);
        setTimeout(function() {
            a.click();
            D.body.removeChild(a);
        }, 66);
        return true;
    } 


    var f = D.createElement("iframe");
    D.body.appendChild(f);
    f.src = "data:" +  strMimeType   + "," + encodeURIComponent(strData);

    setTimeout(function() {
        D.body.removeChild(f);
    }, 333);
    return true;
} 

$('.exportOne').each(function()
{
    $(this).click(function()
    {
        var projectInfo = $('div#' + this.id);
        var project = this.id;
        var participants = "";
        var surveyIdForComments = "";
        if(projectInfo.find('input#participants').is(':checked'))
        {
            participants = projectInfo.find('input#participants').val();
        }
        var surveys = new Array();
        projectInfo.find('input.survey').each(function()
        {
            if($(this).is(':checked'))
            {
                surveys.push(this.id);
            }
            else
            {
            }
        });
        for(var i=0; i<surveys.length; i++)
        {
            if(projectInfo.find('input#' + surveys[i] + '.comments').is(':checked'))
            {
                surveyIdForComments = surveys[i];
            }
        }
        
        $.ajax({
        dataType: 'json',
        type: 'POST',
        url: '/data/exportOneHandler',
        data: {
            project: project,
            participants: participants,
            surveyIdForComments: surveyIdForComments,
            surveys: surveys
        },
        success: function(data) {
            if (data.message == "success")
            {
                    var projname = data.project;
                    var project = data.proj;
                    var projdata = JSON2PSV(project);
                    var survtitle= "";
                    var survdata = "";
                    var comments = data.bulkComment;
                    var commentData = "";
                    var participants = data.bulkParticipant;
                    var participdata = "";
                    var otherMembers = data.otherMembers;
                    var otherMemData = "";
                    
                    //---------------Surveys and Comments under Project By Default Without Any Selections--------------//
                    
                    var dsurveys = data.dbulkSurvey;
                    var dcomments = data.dbulkComment;
                    var dsurvdata = "";
                    var dcommentData = "";
                    var dsurvtitle = "";
                    for(var z=0; z<dcomments.length; z++)
                    {
                        dcomments[z] = JSON2PSV('[{' + dcomments[z].replace(/[,]+$/, '') + '}]');
                        if(z > 0)
                        {
                            dcomments[z] = dcomments[z].substring(dcomments[z].indexOf("\n") + 1);
                        }
                        dcommentData = dcommentData.concat(dcomments[z] + '\n');
                    }
                    
                    for(var i=0; i<dsurveys.length; i++)
                    {
                            dsurvtitle = (dsurveys[i].replace(/[""]/g," ").match("title : (.*) ,")[1]).split(',')[0];
                            dsurveys[i] = JSON2PSV('[{' + dsurveys[i].replace(/[,]+$/, '') + '}]');
                            if(i > 0)
                            {
                                dsurveys[i] = dsurveys[i].substring(dsurveys[i].indexOf("\n") + 1);
                            }
                            dsurvdata = dsurvdata.concat(dsurveys[i] + '\n');
                    }
                    //-----------------------------------------------------------------------------------------------//
                    
                    
                    for(var z=0; z<comments.length; z++)
                    {
                        comments[z] = JSON2PSV('[{' + comments[z].replace(/[,]+$/, '') + '}]');
                        if(z > 0)
                        {
                            comments[z] = comments[z].substring(comments[z].indexOf("\n") + 1);
                        }
                        commentData = commentData.concat(comments[z] + '\n');
                    }
                    
                    for(var y=0; y<otherMembers.length; y++)
                    {
                        otherMembers[y] = JSON2PSV('[{' + otherMembers[y].replace(/[,]+$/, '') + '}]');
                        if(y >= 0)
                        {
                            otherMembers[y] = otherMembers[y].substring(otherMembers[y].indexOf("\n") + 1);
                        }
                        otherMemData = otherMemData.concat(otherMembers[y] + '\n');
                    }
                    
                    var firstline = "";
                    for(var x=0; x<participants.length; x++)
                    {  
                        participants[x] = JSON2PSV('[{' + participants[x].replace(/[,]+$/, '') + '}]');
                        if(x == 0)
                        {
                               firstline = participants[x].split('\n')[0] + '\n';                           
                        }
                        else
                        {
                               participants[x] = participants[x].substring(participants[x].indexOf("\n") + 1);
                        }
                        participdata = participdata.concat(participants[x] + '\n');
                    }
                    var projectMembersData = participdata + otherMemData;
                    
                    if(data.bulkSurvey.length > 0 && data.bulkComment.length == 0)
                    {
                        var surveys = data.bulkSurvey;
                        for(var i=0; i<surveys.length; i++)
                        {
                            survtitle = (surveys[i].replace(/[""]/g," ").match("title : (.*) ,")[1]).split(',')[0];
                            surveys[i] = JSON2PSV('[{' + surveys[i].replace(/[,]+$/, '') + '}]');
                            if(i > 0)
                            {
                                surveys[i] = surveys[i].substring(surveys[i].indexOf("\n") + 1);
                            }
                            survdata = survdata.concat(surveys[i] + '\n');
                        }
                        if(surveys.length == 1)
                        {
                            download(survdata, survtitle + ' of ' + projname + '.txt', 'text/csv');
                        }
                        else if(surveys.length > 1)
                        {
                            download(survdata, projname + ' Surveys.txt', 'text/csv');
                        }
                    }
                    // only one survey comments file at a time //
                    else if(data.bulkSurvey.length == 1 && data.bulkComment.length > 0)
                    {
                        download(commentData, data.surveyWithCommentsTitle + ' Comments.txt', 'text/csv')
                    }
                    
                    // If participant is checked //
                    else if(data.exportParticipants == 'exportParticipants')
                    {
                        download(participdata, projname + ' Participants.txt', 'text/csv');        
                    }
                    
                    // This line zips all information on a project basis //
                    else
                    {   
                            var zip = new JSZip();
                            zip.file(projname + '.txt', projdata);
                            var survs = zip.folder('surveys');
                            survs.file(projname + ' Surveys.txt', dsurvdata);
                            var particips = zip.folder('participants');
                            particips.file(projname + ' Participants.txt', projectMembersData);
                            var comments = zip.folder('comments');
                            comments.file(projname + ' Comments.txt', dcommentData);
                            var content = zip.generate({type: "blob"});
                            saveAs(content, projname + '.zip');
                    }
            }
            else
            {
            }
        },
        error: function() {
            // failed request; give feedback to user
            // alert('Unsuccessful return!');
        }
        });
 
    });
});

$('#exportAll').click(function()
{
        var bulkInfo = $('#data'); 
        var projects = new Array();
        bulkInfo.find('div').each(function()
        {
            projects.push(this.id);
        });
        $.ajax({
        dataType: 'json',
        type: 'POST',
        url: '/data/exportAllHandler',
        data: {
            projects: projects
        },
        success: function(data) {
            if (data.message == "success")
            {
                 var comments = data.bulkComment;
                 var commentData = "";
                 for(var i=0; i<comments.length; i++)
                 {
                     comments[i] = JSON2PSV('[{' + comments[i].replace(/[,]+$/, '') + '}]'); 
                     if(i > 0)
                     {
                            comments[i] = comments[i].substring(comments[i].indexOf("\n") + 1);
                     }
                     commentData = commentData.concat(comments[i] + '\n');
                 }                
                 
                 var videos = data.bulkVideo;
                 var videoData = "";
                 for(var i=0; i<videos.length; i++)
                 {
                     videos[i] = JSON2PSV('[{' + videos[i].replace(/[,]+$/, '') + '}]'); 
                     if(i > 0)
                     {
                            videos[i] = videos[i].substring(videos[i].indexOf("\n") + 1);
                     }
                     videoData = videoData.concat(videos[i] + '\n');
                 }                 
                 
                 var pieces = data.bulkPiece;
                 var pieceData = "";
                 for(var i=0; i<pieces.length; i++)
                 {
                     pieces[i] = JSON2PSV('[{' + pieces[i].replace(/[,]+$/, '') + '}]');
                     if(i > 0)
                     {
                            pieces[i] = pieces[i].substring(pieces[i].indexOf("\n") + 1);
                     }
                     pieceData = pieceData.concat(pieces[i] + '\n');
                 }                 
                 
                 var recordings = data.bulkRecording;
                 var recordingData = "";
                 for(var i=0; i<recordings.length; i++)
                 {
                     for(var j=0; j<recordings[i].length; j++)
                     {
                        recordings[i][j] = JSON2PSV('[{' + recordings[i][j].replace(/[,]+$/, '') + '}]');
                        if(j > 0)
                        {
                            recordings[i][j] = recordings[i][j].substring(recordings[i][j].indexOf("\n") + 1);
                        }
                        recordingData = recordingData.concat(recordings[i][j] + '\n');
                     }
                 }                 
                 
                 var users = data.bulkUser;
                 var userData = "";
                 for(var i=0; i<users.length; i++)
                 {
                     users[i] = JSON2PSV('[{' + users[i].replace(/[,]+$/, '') + '}]'); 
                     if(i > 0)
                     {
                           users[i] = users[i].substring(users[i].indexOf("\n") + 1);
                     }
                     userData = userData.concat(users[i] + '\n');
                 }
                 
                 var otherMembers = data.otherMembers;
                 var otherMemData = "";
                 for(var i=0; i<otherMembers.length; i++)
                 {
                     otherMembers[i] = JSON2PSV('[{' + otherMembers[i].replace(/[,]+$/, '') + '}]');
                     if(i >= 0)
                     {
                        otherMembers[i] = otherMembers[i].substring(otherMembers[i].indexOf("\n") + 1);
                     }
                     otherMemData = otherMemData.concat(otherMembers[i] + '\n');
                 }
  
     
                 var participants = data.bulkParticipant;
                 var participdata = "";
                 var firstline = "";
                 for(var i=0; i<participants.length; i++)
                 {
                     for(var j=0; j<participants[i].length; j++)
                     {
                         participants[i][j] = JSON2PSV('[{' + participants[i][j].replace(/[,"]+$/, '') + '}]');
                         if(j == 0)
                         {
                               firstline = participants[i][j].split('\n')[0] + '\n';                           
                               participants[i][j] = participants[i][j].substring(participants[i][j].indexOf("\n") + 1);
                         }
                         else
                         {
                               participants[i][j] = participants[i][j].substring(participants[i][j].indexOf("\n") + 1);
                         }
                         participdata = participdata.concat(participants[i][j] + '\n');
                     }
                 }
                 var projectMembersData = firstline + participdata + otherMemData;
 
             
                 var projects = data.bulkProject;
                 var projname="";
                 var projdata = "";
                 for (var i = 0; i < projects.length; i++)
                 {
                       projname = (projects[i].replace(/[""]/g, " ").match("name : (.*) ,")[1]).split(',')[0];
                       projects[i] = JSON2PSV('[{' + projects[i].replace(/[,]+$/, '') + '}]');
                       if(i > 0)
                       {
                            projects[i] = projects[i].substring(projects[i].indexOf("\n") + 1);
                       }
                       projdata = projdata.concat(projects[i] + '\n');
                 }
                 
                 var surveys = data.bulkSurvey;
                 var survtitle = "";
                 var survdata = "";
                 for (var i = 0; i < surveys.length; i++)
                 {
                        survtitle = (surveys[i].replace(/[""]/g, " ").match("title : (.*) ,")[1]).split(',')[0];
                        surveys[i] = JSON2PSV('[{' + surveys[i].replace(/[,]+$/, '') + '}]');
                        if(i > 0)
                        {
                            surveys[i] = surveys[i].substring(surveys[i].indexOf("\n") + 1);
                        }
                        survdata = survdata.concat(surveys[i] + '\n');
                 }
                 
                  //download('MERID' + '\n' + projdata + survdata + projectMembersData + userData + recordingData + pieceData + videoData + commentData, 'MERID.psv', 'text/csv');
                    var zip = new JSZip();
                    var projs = zip.folder('projects');
                    projs.file('All Projects.txt', projdata);
                    var records = zip.folder('recordings');
                    records.file('All Recordings.txt', recordingData);
                    var pieces = zip.folder('pieces');
                    pieces.file('All Pieces.txt', pieceData);
                    var vids = zip.folder('videos');
                    vids.file('All Videos.txt', videoData);
                    var comments = zip.folder('comments');
                    comments.file('All Comments.txt', commentData)
                    var users = zip.folder('users');
                    users.file('All Users.txt', userData);
                    var survs = zip.folder('surveys');
                    survs.file('All Surveys.txt', survdata);
                    var particips = zip.folder('participants');
                    particips.file('All Participants.txt', projectMembersData);
                    var content = zip.generate({type: "blob"});
                    saveAs(content, 'MERID.zip');
            }
            else
            {
            }
        },
        error: function() {
            // failed request; give feedback to user
            // alert('Unsuccessful return!');
        }
        });
});
});