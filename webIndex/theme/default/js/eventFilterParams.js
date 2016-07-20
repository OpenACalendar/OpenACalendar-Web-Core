/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software - Website
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
var eventListFilterParams = {};
var eventListFromNowInput;
var eventListFromDiv;
var eventListFromInput;
$(document).ready(function() {
	eventListFromNowInput = $('form.filterListEvent input[name="fromNow"]');
	if (eventListFromNowInput.size() > 0) {
		eventListFromDiv = $('form.filterListEvent #eventListFilterFromWrapper');
		eventListFromInput = $('form.filterListEvent #eventListFilterFromWrapper input');
		if (eventListFromNowInput.attr('checked')) {
			eventListFromDiv.hide();
		} else {
			eventListFromDiv.show();
		}
		eventListFromNowInput.change(function() {
			if (eventListFromNowInput.is(':checked')) {
				eventListFromDiv.hide();
			} else {
				eventListFromDiv.show();
			}
		});
		eventListFromInput.datepicker({
			dateFormat:'d MM yy'
		});
	}
	$('form.filterListEvent input[name="groupSearch"]').autocomplete(
		{
			source:  function( request, response ) {
				$.ajax({
					url: "/api1/groups.json",
					dataType: "json",
					data: {
						titleSearch: request.term, includeDeleted: "no", limit: 20
					},
					success: function( data ) {
						var out = [];
						for(idx in data.data) {
							out.push({ 'label':data.data[idx].title, 'value':data.data[idx].title, 'slug':data.data[idx].slug });
						}
						response( out );
					}
				});
			},
			select: function(event, ui) {
				$('#FilterListEventInputFieldGroupSearch').hide();
				$('#FilterListEventInputFieldGroupLabelWrapper').show();
				$('#FilterListEventInputFieldGroupLabel').text(ui.item.label);
				$('#FilterListEventForm input[name="groupSearchSlug"]').val(ui.item.slug);
			},
			minLength: 1
		}
	);
	$('form.filterListEvent input[name="tagSearch"]').autocomplete(
		{
			source:  function( request, response ) {
				$.ajax({
					url: "/api1/tags.json",
					dataType: "json",
					data: {
						titleSearch: request.term, includeDeleted: "no", limit: 20
					},
					success: function( data ) {
						var out = [];
						for(idx in data.data) {
							out.push({ 'label':data.data[idx].title, 'value':data.data[idx].title, 'slug':data.data[idx].slug });
						}
						response( out );
					}
				});
			},
			select: function(event, ui) {
				$('#FilterListEventInputFieldTagSearch').hide();
				$('#FilterListEventInputFieldTagLabelWrapper').show();
				$('#FilterListEventInputFieldTagLabel').text(ui.item.label);
				$('#FilterListEventForm input[name="tagSearchSlug"]').val(ui.item.slug);
			},
			minLength: 1
		}
	);
	$('form.filterListEvent input[name="countrySearch"]').autocomplete(
		{
			source:  function( request, response ) {
				$.ajax({
					url: "/api1/countries.json",
					dataType: "json",
					data: {
						titleSearch: request.term, limit: 20
					},
					success: function( data ) {
						var out = [];
						for(idx in data.data) {
							out.push({ 'label':data.data[idx].title, 'value':data.data[idx].title, 'twoCharCode':data.data[idx].twoCharCode });
						}
						response( out );
					}
				});
			},
			select: function(event, ui) {
				$('#FilterListEventInputFieldCountrySearch').hide();
				$('#FilterListEventInputFieldCountryLabelWrapper').show();
				$('#FilterListEventInputFieldCountryLabel').text(ui.item.label);
				$('#FilterListEventForm input[name="countrySearchTwoCharCode"]').val(ui.item.twoCharCode);
				$('#FilterListEventInputFieldAreaSearch input').prop('disabled', false);
			},
			minLength: 1
		}
	);
	$('form.filterListEvent input[name="areaSearch"]').autocomplete(
		{
			source:  function( request, response ) {
				var country;
				if (typeof eventListFilterAreaSearchLockedToCountry !== 'undefined' && eventListFilterAreaSearchLockedToCountry) {
					country = eventListFilterAreaSearchLockedToCountry;
				} else {
					country = $('#FilterListEventForm input[name="countrySearchTwoCharCode"]').val();
				}
				$.ajax({
					url: "/api1/country/" + country + "/areas.json",
					dataType: "json",
					data: {
						titleSearch: request.term, includeDeleted: "no", limit: 20
					},
					success: function( data ) {
						var out = [];
						for(idx in data.data) {
							out.push({ 'label':data.data[idx].title, 'value':data.data[idx].title, 'slug':data.data[idx].slug });
						}
						response( out );
					}
				});
			},
			select: function(event, ui) {
				$('#FilterListEventInputFieldAreaSearch').hide();
				$('#FilterListEventInputFieldAreaLabelWrapper').show();
				$('#FilterListEventInputFieldAreaLabel').text(ui.item.label);
				$('#FilterListEventForm input[name="areaSearchSlug"]').val(ui.item.slug);
			},
			minLength: 1
		}
	);
});
eventListFilterParams.onClickInputFieldCountryClear = function() {
	$('#FilterListEventInputFieldCountrySearch input').val('');
	$('#FilterListEventInputFieldCountrySearch').show();
	$('#FilterListEventInputFieldCountryLabelWrapper').hide();
	$('#FilterListEventInputFieldCountryLabel').text('');
	$('#FilterListEventForm input[name="countrySearchTwoCharCode"]').val('');
	$('#FilterListEventInputFieldAreaSearch input').prop('disabled', true);
	eventListFilterParams.onClickInputFieldAreaClear();
}
eventListFilterParams.onClickInputFieldAreaClear = function() {
	$('#FilterListEventInputFieldAreaSearch input').val('');
	$('#FilterListEventInputFieldAreaSearch').show();
	$('#FilterListEventInputFieldAreaLabelWrapper').hide();
	$('#FilterListEventInputFieldAreaLabel').text('');
	$('#FilterListEventForm input[name="areaSearchSlug"]').val('');
}
eventListFilterParams.onClickInputFieldGroupClear = function() {
	$('#FilterListEventInputFieldGroupSearch input').val('');
	$('#FilterListEventInputFieldGroupSearch').show();
	$('#FilterListEventInputFieldGroupLabelWrapper').hide();
	$('#FilterListEventInputFieldGroupLabel').text('');
	$('#FilterListEventForm input[name="groupSearchSlug"]').val('');
}
eventListFilterParams.onClickInputFieldTagClear = function() {
	$('#FilterListEventInputFieldTagSearch input').val('');
	$('#FilterListEventInputFieldTagSearch').show();
	$('#FilterListEventInputFieldTagLabelWrapper').hide();
	$('#FilterListEventInputFieldTagLabel').text('');
	$('#FilterListEventForm input[name="tagSearchSlug"]').val('');
}


