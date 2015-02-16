var SWC = function(swc_accountType, swc_accountId, swc_base_agenda_url, swc_widget_type, swc_widget_template, swc_id, swc_callback) {
	this.id = swc_id;
	this.widget_type = swc_widget_type;
	this.widget_template = swc_widget_template;
	this.callback = swc_callback;
	this.accountType = swc_accountType;
	this.accountId = swc_accountId;
	this.base_agenda_url = swc_base_agenda_url;

	var self = this;
	
	SWC.prototype.events_small = function(count) {
		count = typeof count !== 'undefined' ?  count : 3;

		var base_agenda_param;
		var url;
		
		if (this.base_agenda_url) {
			base_agenda_param = 'base_agenda_url=' + encodeURIComponent(this.base_agenda_url);
		}
		
		url = 
			sowprog_events_widget_parameters.sowprogAPIBaseURL 
			+ '/v1/'
			+ this.accountType + '/'
			+ this.accountId + '/'
			+ 'widget/'
			+ this.widget_type + '/'
			+ this.widget_template +'/'
			+ 'events/small'
			+ '?widget_id=' + this.id;
			if (base_agenda_param) {
				url = url + '&' + base_agenda_param;
			}
		url = url + '&count=' + count;
			
		var script = document.createElement('script');
		script.src = url + '&callback=' + this.callback;
		document.body.appendChild(script);
	};
	
	SWC.prototype.getQueryParams = function (qs) {
	    qs = qs.split("+").join(" ");

	    var params = {}, tokens,
	        re = /[?&]?([^=]+)=([^&]*)/g;

	    while (tokens = re.exec(qs)) {
	        params[decodeURIComponent(tokens[1])]
	            = decodeURIComponent(tokens[2]);
	    }

	    return params;
	};

}

function embedWidgetSowprogSmall(sowprogdata) {
	var destination = document.getElementById("swc_events_small_div");
	if (destination) {
		destination.innerHTML = sowprogdata.html;
	}
};
jQuery(document).ready(
		function() {
			var swc_events_small = new SWC(
					sowprog_events_widget_parameters.userType, 
					sowprog_events_widget_parameters.userID,
					sowprog_events_widget_parameters.agendaPageFullURL, 
					"oembed",
					"basic", 
					"swc_events_small",
					"embedWidgetSowprogSmall");
			swc_events_small.events_small(sowprog_events_widget_parameters.count);
		});
