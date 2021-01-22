<div class="table-responsive">
    <table class="table  table-striped table-sm table-styling" id="tabelstatusss" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th rowspan="2">No </th>
            <th  rowspan="2">Wilayah </th>
            <th  rowspan="2">Nama Rumah Sakit </th>
            <th colspan="10" style="text-align:center">Jumlah Ketersediaan Tempat Tidur </th>

        </tr>
        <tr class="table-inverse">
            <th>VIP </th>
            <th>Kelas I </th>
            <th>Kelas II </th>
            <th>Kelas III </th>
            <th>ICU</th>
            <th>NICU </th>
            <th>PICU </th>
            <th>HCU </th>
            <th>ICCU </th>
            <th>R. Isolasi </th>
        </tr>
        </thead>
        <tbody>
        @foreach($data10 as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d['provinsi'] }}</td>
                <td>{{ $d['namaprofile'] }}</td>
                <td style='background-color:{{ $d['vip'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['vip'] }}</td>
                <td style='background-color:{{ $d['kls1'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['kls1'] }}</td>
                <td style='background-color:{{ $d['kls2'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['kls2'] }}</td>
                <td style='background-color:{{ $d['kls3'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['kls3'] }}</td>
                <td style='background-color:{{ $d['icu'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['icu'] }}</td>
                <td style='background-color:{{ $d['nicu'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['nicu'] }}</td>
                <td style='background-color:{{ $d['picu'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['picu'] }}</td>
                <td style='background-color:{{ $d['hcu'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['hcu'] }}</td>
                <td style='background-color:{{ $d['iccu'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['iccu'] }}</td>
                <td style='background-color:{{ $d['isolasi'] > 0 ? 'rgba(173, 253, 158, 0.53)' :'rgba(255, 163, 163, 0.36)' }}'>{{ $d['isolasi'] }}</td>

            </tr>
        @endforeach

        </tbody>
    </table>
</div>
<script type="text/javascript">

</script>
