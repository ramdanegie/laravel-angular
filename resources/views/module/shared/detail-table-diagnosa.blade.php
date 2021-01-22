<div class="table-responsive">
    <table class="table  table-striped table-sm table-styling" id="tabelstatus" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            <th>Rumah Sakit </th>
            <th>Diagnosa </th>
            <th>No Registrasi </th>
            <th>Tgl Registrasi </th>
            <th>Nama Pasien</th>
            <th>No Rekam Medis</th>
            <th>Kota / Kab </th>
            <th>Provinsi</th>
        </tr>
        </thead>
        <tbody>


        @foreach($data as $i => $d)

        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $d->namaprofile }}</td>
            <td>{{ $d->kddiagnosa .' - ' .$d->namadiagnosa }}</td>
            <td>{{ $d->tglregistrasi }}</td>
            <td>{{ $d->noregistrasi }}</td>
            <td>{{ $d->namapasien }}</td>
            <td>{{ $d->norm }}</td>
            <td>{{ $d->kotakabupaten }}</td>
            <td>{{ $d->provinsi }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(function(){
        $("#tabelstatus").dataTable();
    });
</script>
