google.charts.load( 'current', { 'packages': ['gauge'] } );
google.charts.setOnLoadCallback( wfDrawChart );

function wfGetData() {
	var xmlHttp = new XMLHttpRequest;
	xmlHttp.open( "GET", wFcPanelSettings.dataURI + '?token=' + wFcPanelSettings.token, false );
	xmlHttp.send();
	if ( xmlHttp.status >= 200 && xmlHttp.status < 300 ) {
		var cpdata = JSON.parse( xmlHttp.responseText ).result.data;
		if ( cpdata.hasOwnProperty( 'errors' ) && cpdata.errors != null && cpdata.errors.lenght > 0 ) {
			return { sts: 406 }
		} else {
			imem = cpdata.findIndex( obj => obj.id === 'lvememphy' );
			icpu = cpdata.findIndex( obj => obj.id === 'lvecpu'    );
			ienp = cpdata.findIndex( obj => obj.id === 'lvenproc'  );
			ipro = cpdata.findIndex( obj => obj.id === 'lveep'     );
			iiop = cpdata.findIndex( obj => obj.id === 'lveio'     );
			iops = cpdata.findIndex( obj => obj.id === 'lveiops'   );
			return {
				mem: Math.ceil( 100 * cpdata[ imem ].usage / cpdata[ imem ].maximum ),
				cpu: Math.ceil( 100 * cpdata[ icpu ].usage / cpdata[ icpu ].maximum ),
				epr: Math.max(
					Math.ceil( 100 * cpdata[ ienp ].usage / cpdata[ ienp ].maximum ),
					Math.ceil( 100 * cpdata[ ipro ].usage / cpdata[ ipro ].maximum )
				),
				iop: Math.max(
					Math.ceil( 100 * cpdata[ iiop ].usage / cpdata[ iiop ].maximum ),
					Math.ceil( 100 * cpdata[ iops ].usage / cpdata[ iops ].maximum )
				),
			};
		}
	} else {
		return { sts: xmlHttp.status };
	}
}

function wfDrawChart() {

	var values = wfGetData();

	if ( values.hasOwnProperty( 'sts' ) && values.sts !== 200 ) {
		console.log( wFcPanelSettings.dataURI + ' status ' + values.sts );
	} else {

		var data = google.visualization.arrayToDataTable( [
			[ 'Label', 'Value' ],
			[ wFcPanelSettings.labels.mem, values.mem ],
			[ wFcPanelSettings.labels.cpu, values.cpu ],
			[ wFcPanelSettings.labels.epr, values.epr ],
			[ wFcPanelSettings.labels.iop, values.iop ]
		] );

		const element = document.getElementById( wFcPanelSettings.chartID ).parentElement.parentElement;

		var width   = 1.25 * element.offsetWidth;

		var options = {
				 width: width,   height: width / 5 - 5,
			   redFrom:    90,    redTo:       100,
			yellowFrom:    75, yellowTo:        90,
			minorTicks:     5
		};

		var chart = new google.visualization.Gauge( document.getElementById( wFcPanelSettings.chartID ) );
		chart.draw( data, options );

		setInterval( function() {

			width = 1.25 * element.offsetWidth;

			options.width  = width;
			options.height = width / 5 - 5;

			values = wfGetData();
			if ( ! element.classList.contains( 'closed' ) && element.offsetParent !== null ) {
				data.setValue( 0, 1, values.mem );
				data.setValue( 1, 1, values.cpu );
				data.setValue( 2, 1, values.epr );
				data.setValue( 3, 1, values.iop );
				chart.draw( data, options );
			}
		}, 1000 * wFcPanelSettings.interval );
	}
}
