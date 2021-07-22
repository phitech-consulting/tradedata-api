@extends('layouts.app')

@section('content')
{{--    <h1>Key Indicators</h1>--}}
{{--    <p>An overview of key indicators</p>--}}

    <table>
        @if(count($data['quotes']) > 0)
            @foreach($data['quotes'] as $key => $quote)

                {{-- First iteration print headings --}}
                @if($key == 0)
                    <tr>
                        @foreach($quote as $key => $attribute)
                            <td>{{$key}}</td>
                        @endforeach
                    </tr>
                @endif

                {{-- Print the actual data --}}
                <tr>
                    @foreach($quote as $attribute)
                            <td>{{$attribute['value']}}</td>
                    @endforeach
                </tr>
            @endforeach
        @endif
    </table>
@endsection
