<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rundown Pernikahan - {{ $project->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
            margin: 0 0 5px 0;
        }
        .date {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        .time {
            font-weight: bold;
            color: #16a34a;
            width: 80px;
        }
        .activity {
            font-weight: bold;
            color: #1e293b;
        }
        .description {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }
        .pic {
            width: 120px;
            font-size: 13px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Rundown Hari Pernikahan</h1>
        <p class="date">Acara: {{ $project->name }} &bull; Tanggal: {{ $project->wedding_date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="time">Waktu</th>
                <th>Aktivitas & Keterangan</th>
                <th class="pic">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rundowns as $item)
                <tr>
                    <td class="time">{{ $item->time }}</td>
                    <td>
                        <div class="activity">{{ $item->activity }}</div>
                        @if($item->description)
                            <div class="description">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="pic">{{ $item->assigned_to ?: '-' }}</td>
                </tr>
            @endforeach
            @if($rundowns->isEmpty())
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8; padding: 30px;">
                        Belum ada aktivitas yang dicatat.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
