function createTable(tableID, inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, stat_page) {
  $(tableID).DataTable( {
          paging: inputPaging,
          "searching": inputSearching,
          colReorder: inputColReorder,
          fixedHeader: inputFixedHeader,
          "bInfo": inputBInfo,
          "order": inputSortOrder,
          "pageLength": 250,
          "lengthMenu": [ 10, 25, 50, 75, 100, 250], 
          ajax: {
             url: inputUrl,
             method: "POST",
             data: {
               'page' : stat_page
             }
          },
          columns: inputColumns
      });
}
