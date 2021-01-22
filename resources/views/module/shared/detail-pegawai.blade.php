<div class="table-responsive" style="height: 450px">
    <table class="table  table-striped table-sm table-styling" id="tbl2" style="width:100%">
        <thead>
        <tr class="table-default">
            <th>No </th>
            <th>Nama Lengkap </th>
            <th>Status </th>
            <th>JK </th>
            <th>Tgl Lahir </th>
            <th>Umur </th>
            <th>Pendidikan </th>
        </tr>
        </thead>
        <tbody>

        @foreach($data as $key => $d)

            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $d->namalengkap }}</td>
                <td>{{ $d->statuspegawai }}</td>
                <td>{{ $d->jeniskelamin }}</td>
                <td>{{ $d->tgllahir }}</td>
                <td>{{ $d->umur }}</td>
                <td>{{ $d->pendidikan }}</td>
            </tr>

        @endforeach

        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(function(){
        $("#tbl2").dataTable();
    });
</script>
