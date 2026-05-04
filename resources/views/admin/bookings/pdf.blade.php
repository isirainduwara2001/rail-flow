<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bookings Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #1a1a2e;
            color: #fff;
            padding: 20px 30px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
        }
        .report-meta {
            margin-top: 10px;
            font-size: 10px;
            color: #64748b;
        }
        .stats {
            padding: 15px 30px;
        }
        .stat {
            display: inline-block;
            width: 30%;
            text-align: center;
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
            margin-right: 2%;
            box-sizing: border-box;
        }
        .stat:last-child {
            margin-right: 0;
        }
        .stat .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .stat .value {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .stat.total { border-left: 3px solid #3b82f6; }
        .stat.confirmed { border-left: 3px solid #10b981; }
        .stat.cancelled { border-left: 3px solid #ef4444; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            padding: 0 30px;
        }
        thead th {
            background: #1a1a2e;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        tbody td {
            padding: 8px 10px;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.confirmed {
            background: #d1fae5;
            color: #059669;
        }
        .badge.cancelled {
            background: #fee2e2;
            color: #dc2626;
        }
        .badge.pending {
            background: #fef3c7;
            color: #d97706;
        }
        .badge.completed {
            background: #dbeafe;
            color: #2563eb;
        }
        .footer {
            margin-top: 20px;
            padding: 15px 30px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>RailFlow — Bookings Report</h1>
        <p>Complete bookings management export</p>
        <div class="report-meta">
            <span>Generated: {{ $generatedAt }}</span>
        </div>
    </div>

    <div class="stats">
        <div class="stat total">
            <div class="label">Total Bookings</div>
            <div class="value">{{ $total }}</div>
        </div>
        <div class="stat confirmed">
            <div class="label">Confirmed</div>
            <div class="value">{{ $confirmed }}</div>
        </div>
        <div class="stat cancelled">
            <div class="label">Cancelled</div>
            <div class="value">{{ $cancelled }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Reference</th>
                <th>Passenger</th>
                <th>Train</th>
                <th>Route</th>
                <th>Seat</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Booked</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $index => $booking)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $booking->booking_reference }}</strong></td>
                    <td>{{ $booking->user->name }}</td>
                    <td>{{ $booking->schedule->train->name }}</td>
                    <td>{{ $booking->schedule->from }} - {{ $booking->schedule->to }}</td>
                    <td>{{ $booking->seat->seat_number }}</td>
                    <td>LKR {{ number_format($booking->price, 2) }}</td>
                    <td><span class="badge {{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ date('Y') }} RailFlow — Train Booking System
    </div>

</body>
</html>
