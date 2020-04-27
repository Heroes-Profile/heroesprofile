function createTableAjax(tableID, inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, parameters) {
  $(tableID).DataTable( {
          async: true,
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
             data: parameters
          },
          columns: inputColumns
      });
}
