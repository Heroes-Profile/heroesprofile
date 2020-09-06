function showPop(){
  var dialog = bootbox.dialog({
    title: '<p class="text-center mb-0"></i> Please wait while we grab that data for you.<i class="fas fa-sync fa-spin"></i></p>',
    message: '<p>Data is cached based on the age of the data.  Some filters may take longer than others.</p>',
    closeButton: false
  });

  return dialog;
}
