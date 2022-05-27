@extends('admin.layouts.app')
<script src="https://api-maps.yandex.ru/2.1/?apikey=80e45720-3274-4260-befa-7ea70d8416b4&lang=ru_RU" type="text/javascript">
</script>
@section('content')
    <form action="{{route('hotel.admin.store',['id'=>($row->id) ? $row->id : '-1','lang'=>request()->query('lang')])}}" method="post">
        @csrf
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb20">
                <div class="">
                    <h1 class="title-bar">{{$row->id ? __('Edit: ').$row->title : __('Add new hotel')}}</h1>
                    @if($row->slug)
                        <p class="item-url-demo">{{__("Permalink")}}: {{ url( config('hotel.hotel_route_prefix') ) }}/<a href="#" class="open-edit-input" data-name="slug">{{$row->slug}}</a>
                        </p>
                    @endif
                </div>
                <div class="">
                    @if($row->id)
                        <a class="btn btn-warning btn-xs" href="{{route('hotel.admin.room.index',['hotel_id'=>$row->id])}}" target="_blank"><i class="fa fa-hand-o-right"></i> {{__("Manage Rooms")}}</a>
                    @endif
                    @if($row->slug)
                        <a class="btn btn-primary btn-xs" href="{{$row->getDetailUrl(request()->query('lang'))}}" target="_blank">{{__("View Hotel")}}</a>
                    @endif
                </div>
            </div>
            @include('admin.message')
            @if($row->id)
                @include('Language::admin.navigation')
            @endif
            <div class="lang-content-box">
                <div class="row">
                    <div class="col-md-9">
                        @include('Hotel::admin.hotel.content')
                        @include('Hotel::admin.hotel.pricing')
                        @include('Hotel::admin.hotel.location')
                        @include('Hotel::admin.hotel.surrounding')
                        @include('Core::admin/seo-meta/seo-meta')
                    </div>
                    <div class="col-md-3">
                        <div class="panel">
                            <div class="panel-title"><strong>{{__('Publish')}}</strong></div>
                            <div class="panel-body">
                                @if(is_default_lang())
                                    <div>
                                        <label><input @if($row->status=='publish') checked @endif type="radio" name="status" value="publish"> {{__("Publish")}}
                                        </label></div>
                                    <div>
                                        <label><input @if($row->status=='draft') checked @endif type="radio" name="status" value="draft"> {{__("Draft")}}
                                        </label>
                                    </div>
                                    <div>
                                        <label><input @if($row->status=='pending') checked @endif type="radio" name="status" value="pending"> {{__("Pending")}}
                                        </label>
                                    </div>
                                @endif
                                <div class="text-right">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{__('Save Changes')}}</button>
                                </div>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-title"><strong>Замечания</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <textarea class="form-control" placeholder="Поле для ввода замечаний" maxlength="150" name="remark" id="remark">{{$row->remark}}</textarea>
                                </div>
                                <div class="text-right">
                                    <button class="btn btn-danger" id="delete_remark">Удалить замечания</button>
                                </div>
                            </div>
                        </div>
                        @if(is_default_lang())
                        <div class="panel">
                            <div class="panel-title"><strong>{{__("Author Setting")}}</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <?php
                                    $user = !empty($row->create_user) ? App\User::find($row->create_user) : false;
                                    \App\Helpers\AdminForm::select2('create_user', [
                                        'configs' => [
                                            'ajax'        => [
                                                'url' => route('user.admin.getForSelect2'),
                                                'dataType' => 'json'
                                            ],
                                            'allowClear'  => true,
                                            'placeholder' => __('-- Select User --')
                                        ]
                                    ], !empty($user->id) ? [
                                        $user->id,
                                        $user->getDisplayName() . ' (#' . $user->id . ')'
                                    ] : false)
                                    ?>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(is_default_lang())
                            <div class="panel">
                                <div class="panel-title"><strong>{{__("Availability")}}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>{{__('Hotel Featured')}}</label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="is_featured" @if($row->is_featured) checked @endif value="1"> {{__("Enable featured")}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @include('Hotel::admin.hotel.attributes')

                            <div class="panel">
                                <div class="panel-title"><strong>{{__('Feature Image')}}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id',$row->image_id) !!}
                                    </div>
                                </div>
                            </div>
{{--                            @include('Hotel::admin.hotel.ical')--}}

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section ('script.body')

    <script>
        ymaps.ready(init);

        function init() {
            var suggestView = new ymaps.SuggestView('customPlaceAddress'),
                map,
                placemark;

            if ($('#customPlaceAddress').val() !== '') {
                geocode();
            }

            $('#button_check').bind('click', function (e) {
                event.preventDefault();
                geocode();
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
                        [mapContainer.width(), mapContainer.height()]
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
