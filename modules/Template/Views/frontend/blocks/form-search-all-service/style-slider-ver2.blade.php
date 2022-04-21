
<style type="text/css">
    @media (max-width: 2000px){
.videopc {
  width: 100%;
  height: 100%;
  position: absolute;
  object-fit: cover;
  z-index: 0;
  z-index: -1;}
  .vidos {
    height: 100%;
    background-size: cover;
    background-position: center;}
    
    .text-zagolovka {
    font-weight: 500;
    font-size: 46px;
    color: #FFF;
    }
    .subtext-zagolovka {
    flex: 0 0 100%;
    padding-top: 0;
    font-size: 16px;
    color: #FFF;
    font-weight: 300;
    margin-bottom: -50px;
}}
    @media (max-width: 766px) {
    .text-zagolovka {
    font-size: 20px;
    padding-top: 80px;
    padding-bottom: -80px;}}
</style>
<div class="effect">
    <div class="vidos">
        <video class="videopc" loop="loop" autoplay="" muted="">
        <source src="https://locotrips.ru/uploads/templates/bg22.mp4" type="video/mp4" />
        </video>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-lg-12" style="vertical-align:middle">
            <h1 class="text-zagolovka text-center">Новый взгляд на любимую страну</h1>
            <h2 class="subtext-zagolovka text-center">LocoTrips — всегда с тобой</h2>
            @if(empty($hide_form_search))
                <div class="g-form-control">
                    <ul class="nav nav-tabs" role="tablist">
                        @if(!empty($service_types))
                            @php $number = 0; @endphp
                            @foreach ($service_types as $service_type)
                                @php
                                    $allServices = get_bookable_services();
                                    if(empty($allServices[$service_type])) continue;
                                    $module = new $allServices[$service_type];
                                @endphp
                                <li role="bravo_{{$service_type}}">
                                    <a href="#bravo_{{$service_type}}" class="@if($number == 0) active @endif" aria-controls="bravo_{{$service_type}}" role="tab" data-toggle="tab">
                                        <i class="{{ $module->getServiceIconFeatured() }}"></i>
                                        {{ !empty($modelBlock["title_for_".$service_type]) ? $modelBlock["title_for_".$service_type] : $module->getModelName() }}
                                    </a>
                                </li>
                                @php $number++; @endphp
                            @endforeach
                        @endif
                    </ul>
                    <div class="tab-content">
                        @if(!empty($service_types))
                            @php $number = 0; @endphp
                            @foreach ($service_types as $service_type)
                                @php
                                    $allServices = get_bookable_services();
                                    if(empty($allServices[$service_type])) continue;
                                    $module = new $allServices[$service_type];
                                @endphp
                                <div role="tabpanel" class="tab-pane @if($number == 0) active @endif" id="bravo_{{$service_type}}">
                                    @include(ucfirst($service_type).'::frontend.layouts.search.form-search')
                                </div>
                                @php $number++; @endphp
                            @endforeach
                        @endif<br><br><br>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>