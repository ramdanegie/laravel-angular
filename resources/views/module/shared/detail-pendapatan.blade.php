<div class="table-responsive" style="height: 450px">
    <table class="table  table-striped table-sm table-styling" id="tbl2" style="width:100%">
        <thead>
        <tr class="table-inverse">
            <th>No </th>
            <th>Tgl Pelayanan </th>
            <th>No Registrasi </th>
            <th>No RM </th>
            <th>Nama Pasien </th>
            <th>Jenis </th>
            <th>Total </th>
        </tr>
        </thead>
        <tbody>
        @php
            $total =0;
        @endphp
        @foreach($data as $key => $d)
            @php
                $total =(float)$d->total +$total;
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $d->tglpencarian }}</td>
                <td>{{ $d->noregistrasi }}</td>
                <td>{{ $d->nocm }}</td>
                <td>{{ $d->namapasien }}</td>
                <td>{{ $d->kelompokpasien }}</td>
                <td>{{App\Http\Controllers\MainController::formatRp($d->total)}}</td>
            </tr>

        @endforeach
        <tr style="background:rgba(0,0,0,.3);">
            <td colspan="6">TOTAL</td>
            <td style="text-align: right">{{App\Http\Controllers\MainController::formatRp($total)}}</td>
        </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(function(){
        $("#tbl2").dataTable();
    });
</script>
