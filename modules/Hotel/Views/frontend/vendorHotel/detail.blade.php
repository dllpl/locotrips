@extends('layouts.user')
@section('head')
    <script src="https://api-maps.yandex.ru/2.1/?apikey=80e45720-3274-4260-befa-7ea70d8416b4&lang=ru_RU" type="text/javascript">
    </script>
@endsection
@section('content')
    <h2 class="title-bar no-border-bottom">
        {{$row->id ? __('Edit: ').$row->title : __('Add new hotel')}}
        @if($row->id)
            <div class="title-action">
                <a class="btn btn-info" href="{{route('hotel.vendor.room.index',['hotel_id'=>$row->id])}}">
                    <i class="fa fa-hand-o-right"></i> {{__("Manage Rooms")}}
                </a>
                <a href="{{route('hotel.vendor.room.availability.index',['hotel_id'=>$row->id])}}" class="btn btn-warning">
                    <i class="fa fa-calendar"></i> {{__("Availability Rooms")}}
                </a>
            </div>
        @endif
    </h2>
    @include('admin.message')
    @if($row->id)
        @include('Language::admin.navigation')
    @endif
    <div class="lang-content-box">
        <form action="{{route('hotel.vendor.store',['id'=>($row->id) ? $row->id : '-1','lang'=>request()->query('lang')])}}" method="post">
            @csrf
            <div class="form-add-service">
                <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                    <a data-toggle="tab" href="#nav-tour-content" aria-selected="true" class="active">{{__("1. Content")}}</a>
                    <a data-toggle="tab" href="#nav-tour-location" aria-selected="false">{{__("2. Locations")}}</a>
                    @if(is_default_lang())
                        <a data-toggle="tab" href="#nav-tour-pricing" aria-selected="false">{{__("3. Pricing")}}</a>
                        <a data-toggle="tab" href="#nav-attribute" aria-selected="false">{{__("4. Attributes")}}</a>
{{--                        <a data-toggle="tab" href="#nav-ical" aria-selected="false">{{__("5. Ical")}}</a>--}}
                    @endif
                </div>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-tour-content">
                        @include('Hotel::admin/hotel/content')
                        @if(is_default_lang())
                            <div class="form-group">
                                <label>{{__("Featured Image")}}</label>
                                {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id) !!}
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="nav-tour-location">
                        @include('Hotel::admin/hotel/location',["is_smart_search"=>"1"])
                        @include('Hotel::admin.hotel.surrounding')
                    </div>
                    @if(is_default_lang())
                        <div class="tab-pane fade" id="nav-tour-pricing">
                            @include('Hotel::admin/hotel/pricing')
                        </div>
                        <div class="tab-pane fade" id="nav-attribute">
                            @include('Hotel::admin/hotel/attributes')
                        </div>
{{--                        <div class="tab-pane fade" id="nav-ical">--}}
{{--                            @include('Hotel::admin/hotel/ical')--}}
{{--                        </div>--}}
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{__('Save Changes')}}</button>
            </div>
        </form>
    </div>
@endsection
@section('footer')
    <script type="text/javascript" src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}" ></script>
    <script type="text/javascript" src="{{ asset('js/condition.js?_ver='.config('app.asset_version')) }}"></script>
    <script>
        ymaps.ready(init);

        function init() {
            var suggestView = new ymaps.SuggestView('customPlaceAddress'),
                map,
                placemark;

            if ($('#customPlaceAddress').val() !== '') {
                geocode();
            }

            $('#customPlaceAddress').on('change', function (e) {
                geocode();
            });

            $("#customPlaceAddress").on("keypress", function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    geocode();
                }
            });

            function geocode() {
                var request = $('#customPlaceAddress').val();
                ymaps.geocode(request).then(function (res) {
                    var obj = res.geoObjects.get(0),
                        error, hint;

                    if (obj) {
                        switch (obj.properties.get('metaDataProperty.GeocoderMetaData.precision')) {
                            case 'exact':
                                break;
                            case 'number':
                            case 'near':
                            case 'range':
                                error = 'Неточный адрес, требуется уточнение';
                                hint = 'Уточните номер дома';
                                break;
                            case 'street':
                                error = 'Неполный адрес, требуется уточнение';
                                hint = 'Уточните номер дома';
                                break;
                            case 'other':
                            default:
                                error = 'Неточный адрес, требуется уточнение';
                                hint = 'Уточните адрес';
                        }
                    } else {
                        error = 'Адрес не найден';
                        hint = 'Уточните адрес';
                    }
                    if (error) {
                        showError(error);
                        showMessage(hint);
                    } else {
                        showResult(obj);
                    }
                }, function (e) {
                    console.log(e)
                })

            }
            function showResult(obj) {
                $('#customPlaceAddress').removeClass('input_error');
                $('#notice').css('display', 'none');

                var mapContainer = $('#map_content'),
                    bounds = obj.properties.get('boundedBy'),
                    mapState = ymaps.util.bounds.getCenterAndZoom(
                        bounds,
                        [500, 500]
                    ),
                    address = [obj.getCountry(), obj.getAddressLine()].join(', '),
                    shortAddress = [obj.getThoroughfare(), obj.getPremiseNumber(), obj.getPremise()].join(' ');

                mapState.zoom = {{$row->map_zoom ?? 8}}
                $('input[name=map_lat]').attr('value', mapState.center[0])
                $('input[name=map_lng]').attr('value', mapState.center[1])

                $('input[name=map_zoom]').on('change', function () {
                    mapState.zoom = $('input[name=map_zoom]').val()
                    $('input[name=map_zoom]').attr('value', mapState.zoom)
                    createMap(mapState, shortAddress);
                })
                mapState.controls = [];
                createMap(mapState, shortAddress);
                showMessage(address);
            }

            function showError(message) {
                $('#notice').text(message);
                $('#suggest').addClass('input_error');
                $('#notice').css('display', 'block');
                // Удаляем карту.
                if (map) {
                    map.destroy();
                    map = null;
                }
            }


            function createMap(state, caption) {
                if (!map) {
                    map = new ymaps.Map('map_content', state);
                    placemark = new ymaps.Placemark(
                        map.getCenter(),
                        {
                            hintContent: $('input[name=title]').val(),
                            balloonContent: $('input[name=title]').val() + "<br>" + $('input[name=address]').val()
                        },
                        {
                            iconLayout: 'default#image',
                            iconImageHref: 'https://locotrips.ru/images/icons/png/pin.png',
                            iconImageSize: [26, 42],
                            iconImageOffset: [-17, -50]
                        });
                    MyIconContentLayout = ymaps.templateLayoutFactory.createClass(
                        '<div style="color: #FFFFFF; font-weight: bold;">$[properties.iconContent]</div>'
                    )

                    map.geoObjects.add(placemark);

                } else {
                    map.setCenter(state.center, state.zoom);
                    placemark.geometry.setCoordinates(state.center);
                    placemark.properties.set({iconCaption: caption, balloonContent: caption});
                }


            }

            function showMessage(message) {
                $('#messageHeader').text('Данные получены:');
                $('#message').text(message);
            }
        }

    </script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script>
        $('.attach-demo').sortable({
            items: '.image-item',
            opacity: 0.7,
            containment: 'parent',
            revert: true,
            tolerance: 'pointer',
            update: function() {
                var arr = [];
                $('.image-item a').each(function (i, e) {
                    arr.push($(e).attr('data-id'))
                });
                $('#gallery_input').val(arr)
            }
        })
    </script>
@endsection
