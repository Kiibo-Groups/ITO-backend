<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #3498DB">Codigo</th>
            <th style="font-weight: bold; background-color: #3498DB">Conductor</th>
            <th style="font-weight: bold; background-color: #3498DB">Servicios</th>
            <th style="font-weight: bold; background-color: #3498DB">Km objetivo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->dboy->name }}</td>
                <td>{{ $row->bonus->title }}</td>
                <td>{{ $row->bonus->km_meta }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
