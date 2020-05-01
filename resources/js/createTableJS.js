function createTableJS(tableID, data, inputColumns, columnDefinition, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder) {
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
          data : data,
          columns: inputColumns,
          "columnDefs": columnDefinition
      });
}
