<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Btc Gain Calculator</title>

    {!! Charts::styles() !!}

</head>
<body>

<div class="app">
    <center>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ Form::open(['route' => 'index', 'method' => 'get']) }}
        What if I saved
        {{ Form::number('amount', '20') }}
        in
        {{ Form::select('currency', ['nzd' => 'NZD', 'aud' => 'AUD', 'usd' => 'USD'], 'nzd') }}
        per week per
        {{ Form::select('months', ['6' => '6', '12' => '12', '18' => '18'], '6') }}
        months
        {{ Form::submit('Update!') }}

        {{ Form::close() }}

        {!! $chart->html() !!}

        Total Saved <b>{{ $totalInvestedDolarAmount }} {{ strtoupper($currency) }}</b>&nbsp;&nbsp;
        Btc saved <b>{{ $savedTotalBtcAmount }}</b>&nbsp;&nbsp;
        Gain Percentage <b>{{ $gainPercentage }} %</b>&nbsp;&nbsp;
        Value of Btc <b>{{ $savedTotalDolarAmount }} {{ strtoupper($currency) }}</b>&nbsp;&nbsp;

    </center>
</div>



<!-- End Of Main Application -->
{!! Charts::scripts() !!}
{!! $chart->script() !!}

</body>
</html>