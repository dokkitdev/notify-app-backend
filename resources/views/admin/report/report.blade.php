<img src="{{ $logo }}" alt="logo" style="width: 220px; opacity: .7;">

@foreach($generate as $k => $block)
    <p style="margin-bottom: 20px;">
        Blue Flame Heating Solutions <br>
        Warehouse Report for <b>{{ $k }}</b>
    </p>
    @foreach($block as $g)
        @php ($first = array_first($g['rows']))
        <h3>Job #{{ $first->job_id }}</h3>
        <div>
            Site: {{ $first->site_name }} <br>
            Engineer/s: {{ $first->engineer }}
        </div>
        <table style="width: 100%; margin-top: 30px; width: 100%; border-collapse: collapse;">
            <thead>
            <tr style="background: rgb(206, 223, 242); border: 1px solid black;">
                <th style="border: 1px solid black; padding: 5px; text-align: left;" width="20%">Part No.</th>
                <th style="border: 1px solid black; max-width: 300px; padding: 5px; text-align: left;" width="35%">Stock
                    Name
                </th>
                <th style="border: 1px solid black; padding: 5px; text-align: left;" width="15%">Stored</th>
                <th style="border: 1px solid black; padding: 5px;" width="10%">Required</th>
                <th style="border: 1px solid black; padding: 5px;" width="10%">Assigned</th>
                <th style="border: 1px solid black; padding: 5px;" width="10%">Needed</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($g['rows'] as $key => $a)
                <tr style="background: {{ $key % 2 != 0 ? 'rgb(233, 237, 247);' : 'white;' }} border: 1px solid black;">
                    <td style="border: 1px solid black; padding: 5px; text-align: left;">{{ $a->part_no }}</td>
                    <td style="max-width: 300px; border: 1px solid black; padding: 5px; text-align: left;">{{ $a->stock_name }}</td>
                    <td style="border: 1px solid black; padding: 5px; text-align: left;">{{ $a->storage_location }}</td>
                    <td style="border: 1px solid black; padding: 5px; text-align: center;">{{ $a->required }}</td>
                    <td style="border: 1px solid black; padding: 5px; text-align: center;">{{ $a->assigned }}</td>
                    <td style="border: 1px solid black; padding: 5px; text-align: center;">{{ $a->getNeeded() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
    <p style="page-break-after: always"></p>
    <p><br></p>
@endforeach