@php($location_search_style = setting_item('space_location_search_style'))
<div class="filter-item">
    <div class="form-group">
        <i class="field-icon fa icofont-map"></i>
        <div class="form-content">
            @if($location_search_style=='autocompletePlace')
                <div class="g-map-place">
                    <input type="text" name="city_dadata" placeholder="{{__("Where are you going?")}}"
                           value="{{request()->input('map_place')}}" class="form-control border-0" id="city_dadata">
                    <form action="{{route('space.markers_by_city')}}" id="city_form" style="display: none">
                        <input type="hidden" name="dadata_city" value="" id="dadata_city">
                    </form>
{{--                    <div class="map d-none" id="map-{{\Illuminate\Support\Str::random(10)}}"></div>--}}
{{--                    <input type="hidden" name="map_lat" value="{{request()->input('map_lat')}}">--}}
{{--                    <input type="hidden" name="map_lgn" value="{{request()->input('map_lgn')}}">--}}
                </div>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
                <link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/css/suggestions.min.css" rel="stylesheet" />
                <script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/js/jquery.suggestions.min.js"></script>
                <script>
                    var token = "a963decbb7de0685da0de94aa3655786ea5a336d";

                    var defaultFormatResult = $.Suggestions.prototype.formatResult;

                    function formatResult(value, currentValue, suggestion, options) {
                        var newValue = suggestion.data.city;
                        suggestion.value = newValue;
                        return defaultFormatResult.call(this, newValue, currentValue, suggestion, options);
                    }

                    function formatSelected(suggestion) {
                        return suggestion.data.city;
                    }

                    $("#city_dadata").suggestions({
                        token: token,
                        type: "ADDRESS",
                        hint: false,
                        bounds: "city",
                        constraints: {
                            locations: { city_type_full: "город" }
                        },
                        formatResult: formatResult,
                        formatSelected: formatSelected,
                        onSelect: function(suggestion) {
                            $('#dadata_city').val(suggestion.data.city)
                            $('#city_form').submit();
                        }
                    });
                </script>

            @else
                <?php
                $location_name = "";
                $list_json = [];
                $traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json, &$location_name) {
                    foreach ($locations as $location) {
                        $translate = $location->translateOrOrigin(app()->getLocale());
                        if (request()->query('location_id') == $location->id) {
                            $location_name = $translate->name;
                        }
                        $list_json[] = [
                            'id' => $location->id,
                            'title' => $prefix . ' ' . $translate->name,
                        ];
                        $traverse($location->children, $prefix . '-');
                    }
                };
                $traverse($list_location);
                ?>
                <div class="smart-search">
                    <input type="text" class="smart-search-location parent_text form-control"
                           {{ ( empty(setting_item("space_location_search_style")) or setting_item("space_location_search_style") == "normal" ) ? "readonly" : ""  }} placeholder="{{__("Where are you going?")}}"
                           value="{{ $location_name }}" data-onLoad="{{__("Loading...")}}"
                           data-default="{{ json_encode($list_json) }}">
                    <input type="hidden" class="child_id" name="location_id" value="{{Request::query('location_id')}}">
                </div>
            @endif
        </div>
    </div>
</div>
