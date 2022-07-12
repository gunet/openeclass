
// function open_document(){
//     $('.fileModal').click(function (e)
//     {
//         e.preventDefault();
//         var fileURL = $(this).attr('href');
//         var downloadURL = $(this).prev('input').val();
//         var fileTitle = $(this).attr('title');
//         console.log('the fileURL:'+fileURL);
//         console.log('the downloadURL:'+downloadURL);
//         // BUTTONS declare
//         var bts = {
//             download: {
//                 label: '<span class="fa fa-download"></span> Ληψη',
//                 className: 'btn-success',
//                 callback: function (d) {
//                     window.location = downloadURL;
//                 }
//             },
//             print: {
//                 label: '<span class="fa fa-print"></span> Εκτυπωση',
//                 className: 'btn-primary',
//                 callback: function (d) {
//                     var iframe = document.getElementById('fileFrame');
//                     iframe.contentWindow.print();
//                 }
//             }
//         };
//         if (screenfull.enabled) {
//             bts.fullscreen = {
//                 label: '<span class="fa fa-arrows-alt"></span> Πληρης οθονη',
//                 className: 'btn-primary',
//                 callback: function() {
//                     screenfull.request(document.getElementById('fileFrame'));
//                     return false;
//                 }
//             };
//         }
//         bts.newtab = {
//             label: '<span class="fa fa-plus"></span> Νεο παραθυρο',
//             className: 'btn-primary',
//             callback: function() {
//                 window.open(fileURL);
//                 return false;
//             }
//         };
//         bts.cancel = {
//             label: 'Ακυρωση',
//             className: 'btn-default'
//         };

//         bootbox.dialog({
//             size: 'large',
//             title: fileTitle,
//             message: '<div class="row">'+
//                         '<div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">'+
//                             '<div class="iframe-container" style="height:500px;"><iframe id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
//                         '</div>'+
//                     '</div>',
//             buttons: bts
//         });
//     });
// }

// $(document).ready(function () {
//     open_document();
// });
