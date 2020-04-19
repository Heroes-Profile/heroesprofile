function createTable(inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder) {
  $('#table').DataTable( {
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
             method: "POST"
          },
          columns: inputColumns
      });
}
