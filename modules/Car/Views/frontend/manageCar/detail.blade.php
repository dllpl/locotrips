@extends('layouts.user')
@section('head')
    <script src="https://api-maps.yandex.ru/2.1/?apikey=80e45720-3274-4260-befa-7ea70d8416b4&lang=ru_RU" type="text/javascript">
    </script>
@endsection
@section('content')
    <h2 class="title-bar no-border-bottom">
        {{$row->id ? __('Edit: ').$row->title : __('Add new car')}}
    </h2>
    @include('admin.message')
    @if($row->id)
        @include('Language::admin.navigation')
    @endif
    <div class="lang-content-box">
        <form action="{{route('car.vendor.store',['id'=>($row->id) ? $row->id : '-1','lang'=>request()->query('lang')])}}" method="post">
            @csrf
            <div class="form-add-service">
                <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                    <a data-toggle="tab" href="#nav-tour-content" aria-selected="true" class="active">{{__("1. Content")}}</a>
                    <a data-toggle="tab" href="#nav-tour-location" aria-selected="false">{{__("2. Locations")}}</a>
                    <a data-toggle="tab" href="#nav-tour-pricing" aria-selected="false">{{__("3. Pricing")}}</a>
                    @if(is_default_lang())
                        <a data-toggle="tab" href="#nav-attribute" aria-selected="false">{{__("4. Attributes")}}</a>
                    @endif
                </div>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-tour-content">
                        @include('Car::admin/car/content')
                        @if(is_default_lang())
                            <div class="form-group">
                                <label>{{__("Featured Image")}}</label>
                                {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id) !!}
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="nav-tour-location">
                        @include('Car::admin/car/location',["is_smart_search"=>"1"])
                    </div>
                    <div class="tab-pane fade" id="nav-tour-pricing">
                        <div class="panel">
                            <div class="panel-title"><strong>{{__('Default State')}}</strong></div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <select name="default_state" class="custom-select">
                                                <option value="">{{__('-- Please select --')}}</option>
                                                <option value="1" @if(old('default_state',$row->default_state ?? 0) == 1) selected @endif>{{__("Always available")}}</option>
                                                <option value="0" @if(old('default_state',$row->default_state ?? 0) == 0) selected @endif>{{__("Only available on specific dates")}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @include('Car::admin/car/pricing')
                    </div>
                    @if(is_default_lang())
                        <div class="tab-pane fade" id="nav-attribute">
                            @include('Car::admin/car/attributes')
                        </div>
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
@endsection
