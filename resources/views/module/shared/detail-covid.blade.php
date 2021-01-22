<div class="table-responsive" style="height: 450px">
    <table class="table  table-striped table-sm table-styling" id="tabelstatus22" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            <th>No Rekam Medis </th>
            <th>Nama Pasien </th>
            <th>No Registrasi </th>
            <th>Tgl Registrasi </th>
        </tr>
        </thead>
        <tbody>
        @php

        @endphp
        @foreach($data->groupBy('namaprofile') as $year => $student)
            @php
                $produk ='';
            @endphp
            @foreach( $student as $key => $d)
            @if($produk != $d->namaprofile)
                <tr>
                    <td bgcolor='#666666' align='center' height='25' width='1%'
                        style='color:white;text-align: left;' colspan="5">
                        <p style='margin-left:10px'><font style='font-size: 9pt' face='Arial' color='#FFFFFF'>
                                <b>{{ $student[0]->namaprofile }} : Jumlah [ {{ count($student) }} ]</b></font></td>
                </tr>

                @php
                    $produk = $d->namaprofile;
                @endphp
            @endif


            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $d->norm }}</td>
                <td>{{ $d->namapasien }}</td>
                <td>{{ $d->noregistrasi }}</td>
                <td>{{ $d->tglregistrasi }}</td>
            </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(function(){
        $("#tabelstatus22").dataTable();
    });
</script>
