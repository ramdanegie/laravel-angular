<div class="table-responsive">
    <table class="table  table-striped table-sm table-styling" id="tabelstsatus" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            <th>Wilayah </th>
            <th>Rumah Sakit </th>
            <th>Jumlah Ketersediaan Tempat Tidur</th>
            <th>Update Terakhir</th>
        </tr>
        </thead>
        <tbody>
        @forelse($data as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->provinsi }}</td>
                <td>{{ $d->namaprofile }}</td>
                <td>{{ $d->tersedia }}</td>
                <td>{{ $d->tglupdate }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center">Data Tidak ada</td>

            </tr>
        @endforelse
        </tbody>
    </table>
</div>
<script type="text/javascript">
    // $(function(){
    //     $("#tabelstsatus").dataTable();
    // });
</script>
