<div class="table-responsive">
    <table class="table  table-striped table-sm table-styling" id="tabelstatus2132" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            <th>Nama Produk </th>
            <th>Satuan </th>
            <th>Stok</th>
            <th>Rumah Sakit </th>
            <th>Update Terakhir </th>
        </tr>
        </thead>
        <tbody>
            @php

      $sama = false;
      $groupingArr = [];

      for ($i = 0; $i <  count($data2); $i++) {
          $sama = false;
            for ($x = 0; $x <  count($groupingArr); $x++) {
              if ($data2[$i]['namaprofile'] == $groupingArr[$x]['namaprofile']
               && $data2[$i]['namaproduk'] == $groupingArr[$x]['namaproduk']) {
                $sama = true;
                $groupingArr[$x]['total']= (float)$data2[$i]['total'] + (float)$groupingArr[$x]['total'];
              }
            }
            if ($sama == false) {
              $groupingArr[] = $data2[$i];
            }
      }
   // dd($data2);

            @endphp
        @foreach($groupingArr as $i => $d)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $d['namaproduk'] }}</td>
            <td>{{ $d['satuanstandar'] }}</td>
            <td>{{ $d['total'] }}</td>
            <td>{{ $d['namaprofile'] }}</td>
            <td>{{ $d['tglupdate'] }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(function(){
        $("#tabelstatus2132").dataTable();
    });
</script>
