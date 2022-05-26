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
        ymaps.ready(function () {
            let myMap = new ymaps.Map('map_content', {
                    center: [{{$row->map_lat ?? setting_item('map_lat_default')}}, {{$row->map_lng ?? setting_item('map_lng_default')}}],
                    zoom: 13
                }, {
                    searchControlProvider: 'yandex#search'
                })

            let map_lat = {{$row->map_lat ?? setting_item('map_lat_default')}};
            let map_lng = {{$row->map_lng ?? setting_item('map_lng_default')}};

            MyIconContentLayout = ymaps.templateLayoutFactory.createClass(
                '<div style="color: #FFFFFF; font-weight: bold;">$[properties.iconContent]</div>'
            )

            $("input[name=map_lat], input[name=map_lng]").on('change', function () {
                myMap.geoObjects.removeAll()
                map_lat = $('input[name=map_lat]').val();
                map_lng = $('input[name=map_lng]').val();
                myPlacemark = new ymaps.Placemark([map_lat,map_lng],
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
                myMap.geoObjects.add(myPlacemark);
                myMap.setCenter([map_lat,map_lng],myMap.getZoom())
            })
        });


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
