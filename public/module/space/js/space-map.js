jQuery(function ($) {


	$(".bravo-filter-price").each(function () {
		var input_price = $(this).find(".filter-price");
		var min = input_price.data("min");
		var max = input_price.data("max");
		var from = input_price.data("from");
		var to = input_price.data("to");
		var symbol = input_price.data("symbol");
		input_price.ionRangeSlider({
			type: "double",
			grid: true,
			min: min,
			max: max,
			from: from,
			to: to,
			prefix: symbol
		});
	});

    var markers = bravo_map_data.markers
    var myMap;

    ymaps.ready(init);

    function init() {
        myMap = new ymaps.Map("bravo_results_map", {
            center: [bravo_map_data.map_lat_default, bravo_map_data.map_lng_default],
            zoom: 5,
            controls: ["zoomControl"],
            zoomMargin: [20],
        });
        for (var i = 0; i < markers.length; i++) {
            var cityCollection = new ymaps.GeoObjectCollection();
            var shopPlacemark = new ymaps.Placemark([markers[i].lat,markers[i].lng], {
                balloonContentBody: markers[i].infobox
            }, {
                iconLayout: "default#image",
                iconImageHref: markers[i].marker
            });
            cityCollection.add(shopPlacemark);
            myMap.geoObjects.add(cityCollection);
        }
        myMap.setBounds(cityCollection.getBounds(), {
            checkZoomRange: true,
        }).then(function () {myMap.setZoom(2)});
    }

    function addMarkers(markers) {
        for (var i = 0; i < markers.length; i++) {
            var cityCollection = new ymaps.GeoObjectCollection();
            var shopPlacemark = new ymaps.Placemark([markers[i].lat,markers[i].lng], {
                balloonContentBody: markers[i].infobox
            }, {
                iconLayout: "default#image",
                iconImageHref: markers[i].marker
            });
            cityCollection.add(shopPlacemark);
            myMap.geoObjects.add(cityCollection);
        }
        myMap.setBounds(cityCollection.getBounds(), {
                checkZoomRange: true,
            }).then(function () {myMap.setZoom(10)});
    }

	$('.bravo_form_search_map .smart-search .child_id').change(function () {
		reloadForm();
	});
    $('.bravo_form_search_map .g-map-place input[name=datata_address_hidden]').change(function () {
        setTimeout(function () {
            reloadForm()
        },500)
    });
	$('.bravo_form_search_map .input-filter').change(function () {
		reloadForm();
	});
	$('.bravo_form_search_map .btn-filter,.btn-apply-advances').click(function () {
		reloadForm();
	});
	$('.btn-apply-advances').click(function(){
		$('#advance_filters').addClass('d-none');
	})

	function reloadForm(){
		$('.map_loading').show();
		$.ajax({
			data:$('.bravo_form_search_map input,select,textarea,input:hidden,#advance_filters input,select,textarea').serialize()+'&_ajax=1',
			url:window.location.href.split('?')[0],
			dataType:'json',
			type:'get',
			success:function (json) {
				$('.map_loading').hide();
				if(json.status)
				{
                    myMap.geoObjects.removeAll()
					addMarkers(json.markers);

					$('.bravo-list-item').replaceWith(json.html);

					$('.listing_items').animate({
                        scrollTop:0
                    },'fast');

					if(window.lazyLoadInstance){
						window.lazyLoadInstance.update();
					}

				}

			},
			error:function (e) {
				$('.map_loading').hide();
				if(e.responseText){
					$('.bravo-list-item').html('<p class="alert-text danger">'+e.responseText+'</p>')
				}
			}
		})
	}

	function reloadFormByUrl(url){
        $('.map_loading').show();
        $.ajax({
            url:url,
            dataType:'json',
            type:'get',
            success:function (json) {
                $('.map_loading').hide();
                if(json.status)
                {
                    myMap.geoObjects.removeAll()
                    addMarkers(json.markers);

                    $('.bravo-list-item').replaceWith(json.html);

					setTimeout(function () {
						$('.listing_items').animate({
							scrollTop:0
						},'fast');
						if($(document).width() < 991){
							$('html,body').animate({
								scrollTop: $(".listing_items").offset().top - 50
							},'fast');
						}
					},500);

                    if(window.lazyLoadInstance){
                        window.lazyLoadInstance.update();
                    }
                }

            },
            error:function (e) {
                $('.map_loading').hide();
                if(e.responseText){
                    $('.bravo-list-item').html('<p class="alert-text danger">'+e.responseText+'</p>')
                }
            }
        })
	}

	$('.toggle-advance-filter').click(function () {
		var id = $(this).data('target');
		$(id).toggleClass('d-none');
	});

    $(document).on('click', '.filter-item .dropdown-menu', function (e) {

        if(!$(e.target).hasClass('btn-apply-advances')){
            e.stopPropagation();
		}
    })
		.on('click','.bravo-pagination a',function (e) {
			e.preventDefault();
            reloadFormByUrl($(this).attr('href'));
        })
	;

});
