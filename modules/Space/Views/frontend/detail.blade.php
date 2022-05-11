@extends('layouts.app')
@section('head')
    <link href="{{ asset('dist/frontend/module/space/css/space.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/fotorama/fotorama.css") }}"/>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=80e45720-3274-4260-befa-7ea70d8416b4&lang=ru_RU" type="text/javascript">
    </script>
@endsection
@section('content')
    <div class="bravo_detail_space">
        @include('Space::frontend.layouts.details.space-banner')
        <div class="bravo_content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-lg-9">
                        @php $review_score = $row->review_data @endphp
                        @include('Space::frontend.layouts.details.space-detail')
                        @include('Space::frontend.layouts.details.space-review')
                    </div>
                    <div class="col-md-12 col-lg-3">
                        @include('Tour::frontend.layouts.details.vendor')
                        @include('Space::frontend.layouts.details.space-form-book')
                    </div>
                </div>
                <div class="row end_tour_sticky">
                    <div class="col-md-12">
                        @include('Space::frontend.layouts.details.space-related')
                    </div>
                </div>
            </div>
        </div>
        @include('Space::frontend.layouts.details.space-form-book-mobile')
    </div>
@endsection

@section('footer')
{{--    {!! App\Helpers\MapEngine::scripts() !!}--}}
{{--            <script>--}}
{{--                 jQuery(function ($) {--}}
{{--@if($row->map_lat && $row->map_lng)--}}
{{--        new BravoMapEngine('map_content', {--}}
{{--            disableScripts: true,--}}
{{--            fitBounds: true,--}}
{{--            center: [{{$row->map_lat}}, {{$row->map_lng}}],--}}
{{--                zoom:{{$row->map_zoom ?? "8"}},--}}
{{--                ready: function (engineMap) {--}}
{{--                    engineMap.addMarker([{{$row->map_lat}}, {{$row->map_lng}}], {--}}
{{--                        icon_options: {--}}
{{--                            iconUrl:"{{get_file_url(setting_item("space_icon_marker_map"),'full') ?? url('images/icons/png/pin.png') }}"--}}
{{--                        }--}}
{{--                    });--}}
{{--                }--}}
{{--            });--}}
{{--            @endif--}}
{{--            })--}}
{{--        </script>--}}
    <script type="text/javascript">
        ymaps.ready(function () {
            var myMap = new ymaps.Map('map_content', {
                    center: [{{$row->map_lat}}, {{$row->map_lng}}],
                    zoom: 13
                }, {
                    searchControlProvider: 'yandex#search'
                }),

                // Создаём макет содержимого.
                MyIconContentLayout = ymaps.templateLayoutFactory.createClass(
                    '<div style="color: #FFFFFF; font-weight: bold;">$[properties.iconContent]</div>'
                ),

                myPlacemark = new ymaps.Placemark(myMap.getCenter(), {
                    hintContent: '{!! clean($translation->title) !!}',
                    balloonContent: '{!! clean($translation->title) !!} по адресу {{$translation->address}}'
                }, {
                    iconLayout: 'default#image',
                    iconImageHref: 'https://locotrips.ru/images/icons/png/pin.png',
                    iconImageSize: [26, 42],
                    // Смещение левого верхнего угла иконки относительно
                    // её "ножки" (точки привязки).
                    iconImageOffset: [-17, -50]
                });

            myMap.geoObjects
                .add(myPlacemark);
        });
    </script>

    <script>
        var bravo_booking_data = {!! json_encode($booking_data) !!}
            var bravo_booking_i18n = {
            no_date_select:'{{__('Please select Start and End date')}}',
            no_guest_select:'{{__('Please select at least one guest')}}',
            load_dates_url:'{{route('space.vendor.availability.loadDates')}}',
            name_required:'{{ __("Name is Required") }}',
            email_required:'{{ __("Email is Required") }}',
        };
    </script>
    <script type="text/javascript" src="{{ asset("libs/ion_rangeslider/js/ion.rangeSlider.min.js") }}"></script>
    <script type="text/javascript" src="{{ asset("libs/fotorama/fotorama.js") }}"></script>
    <script type="text/javascript" src="{{ asset("libs/sticky/jquery.sticky.js") }}"></script>
    <script type="text/javascript" src="{{ asset('module/space/js/single-space.js?_ver='.config('app.asset_version')) }}"></script>
@endsection
