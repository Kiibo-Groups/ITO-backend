<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #3498DB">ID</th>
            <th style="font-weight: bold; background-color: #3498DB">Cliente</th>
            <th style="font-weight: bold; background-color: #3498DB">Origen</th>
            <th style="font-weight: bold; background-color: #3498DB">Destino</th>
            <th style="font-weight: bold; background-color: #3498DB">Conductor</th>
            <th style="font-weight: bold; background-color: #3498DB">Costo del viaje</th>
            <th style="font-weight: bold; background-color: #3498DB">Costo extra</th>
            <th style="font-weight: bold; background-color: #3498DB">SubTotal</th>
            <th style="font-weight: bold; background-color: #3498DB">Total</th>
            <th style="font-weight: bold; background-color: #3498DB">Comisión</th>
            <th style="font-weight: bold; background-color: #3498DB">Tipo de pago</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->user->name }}</td>
                <td>
                    <a href="http://maps.google.com/?q={{ $row->address_origin }}" target="_blank">
                        {{ $row->address_origin }}
                    </a>
                </td>
                <td>
                    <a href="http://maps.google.com/?q={{ $row->address_destin }}" target="_blank">
                        {{ $row->address_destin }}
                    </a>
                </td>
                <td>{{ $row->dboy->name }}</td>
                <td>{{ number_format($row->d_charges,2) }}</td>
                <td>{{ number_format($row->extra_charge,2) }}</td>
                <td>{{ number_format($row->total,2) }}</td>
                <td>{{ number_format(($row->total + $row->extra_charge),2) }}</td>
                <td>{{ $row->price_comm }}</td>
                <td>
                    @if ($row->payment_method == 1)
                        Efectivo
                    @elseif($row->payment_method == 2)
                        Saldo Disponible
                    @elseif($row->payment_method == 3)
                        Tarjeta
                    @else
                        Efectivo
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
