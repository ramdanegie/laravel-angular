<div class="table-responsive">
    <table class="table  table-striped table-sm table-styling" id="tabelstatus" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            <th>Rumah Sakit </th>
            <th>Jumlah </th>
        </tr>
        </thead>
        <tbody>
        @forelse($data as $i => $d)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $d->namaprofile }}</td>
            <td>{{ $d->jumlah }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" style="text-align: center">Data Tidak ada</td>

        </tr>
        @endforelse
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(function(){
        $("#tabelstatus").dataTable();
    });
</script>
