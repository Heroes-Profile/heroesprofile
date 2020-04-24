function createTableJS(tableID, data, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, stat_page) {
  $(tableID).DataTable( {
          paging: inputPaging,
          "searching": inputSearching,
          colReorder: inputColReorder,
          fixedHeader: inputFixedHeader,
          "bInfo": inputBInfo,
          "order": inputSortOrder,
          "pageLength": 250,
          "lengthMenu": [ 10, 25, 50, 75, 100, 250],
          data : data,
          columns: inputColumns
      });
}
