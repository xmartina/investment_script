//[Dashboard Javascript]

//Project:	Crypto Admin - Responsive Admin Template
//Primary use:   Used only for the main dashboard (index.html)


$(function () {

  'use strict';
	

	
		var options = {
          series: [{
          name: 'PRODUCT A',
          data: [44, 55, 41, 67, 22, 43, 44, 55, 41, 67, 22, 43]
        }, {
          name: 'PRODUCT B',
          data: [-44, -55, -41, -67, -22, -43, -44, -55, -41, -67, -22, -43]
        }],
          chart: {
		  foreColor:"#bac0c7",
          type: 'bar',
          height: 350,
          stacked: true,
          toolbar: {
            show: false
          },
          zoom: {
            enabled: true
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            legend: {
              position: 'bottom',
              offsetX: -10,
              offsetY: 0
            }
          }
        }],		
		grid: {
			show: true,
			borderColor: '#f7f7f7',      
		},
		colors:['#6993ff', '#f64e60'],
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '20%',
            endingShape: 'rounded'
          },
        },
        dataLabels: {
          enabled: false
        },
 
        xaxis: {
          type: 'datetime',
          categories: ['01/01/2011 GMT', '01/02/2011 GMT', '01/03/2011 GMT', '01/04/2011 GMT',
            '01/05/2011 GMT', '01/06/2011 GMT', '01/07/2011 GMT', '01/08/2011 GMT', '01/09/2011 GMT', '01/10/2011 GMT',
            '01/11/2011 GMT', '01/12/2011 GMT'
          ],
        },
        legend: {
          show: false,
        },
        fill: {
          opacity: 1
        }
        };

        var chart = new ApexCharts(document.querySelector("#summary-chart"), options);
        chart.render();
	
	
	
	// Apex  start
  if($('#apexChart2').length) {
    var options2 = {
      chart: {
        type: "bar",
        height: 100,
        sparkline: {
          enabled: !0
        }
      },
      plotOptions: {
        bar: {
          columnWidth: "25%"
        }
      },
      colors: ["#ffffff"],
      series: [{
        data: [36, 77, 52, 90, 74, 35, 55, 23, 47, 10, 63, 36, 77, 52, 90, 74, 35, 55, 23, 47]
      }],
      labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
      xaxis: {
        crosshairs: {
          width: 2
        }
      },
      tooltip: {
        fixed: {
          enabled: !1
        },
        x: {
          show: !1
        },
        y: {
          title: {
            formatter: function(e) {
              return ""
            }
          }
        },
        marker: {
          show: !1
        }
      }
    };
    new ApexCharts(document.querySelector("#apexChart2"),options2).render();
  }
  // Apex  end
	
	
	
	// Apex  start
  if($('#apexChart3').length) {
    var options2 = {
      chart: {
        type: "bar",
        height: 100,
        sparkline: {
          enabled: !0
        }
      },
      plotOptions: {
        bar: {
          columnWidth: "25%"
        }
      },
      colors: ["#ffffff"],
      series: [{
        data: [36, 77, 52, 90, 74, 35, 55, 23, 47, 10, 63, 36, 77, 52, 90, 74, 35, 55, 23, 47]
      }],
      labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
      xaxis: {
        crosshairs: {
          width: 2
        }
      },
      tooltip: {
        fixed: {
          enabled: !1
        },
        x: {
          show: !1
        },
        y: {
          title: {
            formatter: function(e) {
              return ""
            }
          }
        },
        marker: {
          show: !1
        }
      }
    };
    new ApexCharts(document.querySelector("#apexChart3"),options2).render();
  }
	
  // Apex chart2 end
	
	
	
	
am5.ready(function() {

// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("order-chart");

// Set themes
// https://www.amcharts.com/docs/v5/concepts/themes/
root.setThemes([
  am5themes_Animated.new(root)
]);

// Create chart
// https://www.amcharts.com/docs/v5/charts/xy-chart/
var chart = root.container.children.push(
  am5xy.XYChart.new(root, {
    focusable: true,
    panX: false,
    panY: false,
    wheelX: "none",
    wheelY: "none"
  })
);

// Chart title
var title = chart.plotContainer.children.push(am5.Label.new(root, {
  text: "Price (BTC/ETH)",
  fontSize: 20,
  fontWeight: "400",
  x: am5.p50,
  centerX: am5.p50
}))

// Create axes
// https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
  categoryField: "value",
  renderer: am5xy.AxisRendererX.new(root, {
    minGridDistance: 70
  }),
  tooltip: am5.Tooltip.new(root, {})
}));

xAxis.get("renderer").labels.template.adapters.add("text", function(text, target) {
  if (target.dataItem) {
    return root.numberFormatter.format(Number(target.dataItem.get("category")), "#.####");
  }
  return text;
});

var yAxis = chart.yAxes.push(
  am5xy.ValueAxis.new(root, {
    maxDeviation: 0.1,
    renderer: am5xy.AxisRendererY.new(root, {})
  })
);

// Add series
// https://www.amcharts.com/docs/v5/charts/xy-chart/series/

var bidsTotalVolume = chart.series.push(am5xy.StepLineSeries.new(root, {
  minBulletDistance: 10,
  xAxis: xAxis,
  yAxis: yAxis,
  valueYField: "bidstotalvolume",
  categoryXField: "value",
  stroke: am5.color(0x00ff00),
  fill: am5.color(0x00ff00),
  tooltip: am5.Tooltip.new(root, {
    pointerOrientation: "horizontal",
    labelText: "[width: 120px]Ask:[/][bold]{categoryX}[/]\n[width: 120px]Total volume:[/][bold]{valueY}[/]\n[width: 120px]Volume:[/][bold]{bidsvolume}[/]"
  })
}));
bidsTotalVolume.strokes.template.set("strokeWidth", 2)
bidsTotalVolume.fills.template.setAll({
  visible: true,
  fillOpacity: 0.2
});

var asksTotalVolume = chart.series.push(am5xy.StepLineSeries.new(root, {
  minBulletDistance: 10,
  xAxis: xAxis,
  yAxis: yAxis,
  valueYField: "askstotalvolume",
  categoryXField: "value",
  stroke: am5.color(0xf00f00),
  fill: am5.color(0xff0000),
  tooltip: am5.Tooltip.new(root, {
    pointerOrientation: "horizontal",
    labelText: "[width: 120px]Ask:[/][bold]{categoryX}[/]\n[width: 120px]Total volume:[/][bold]{valueY}[/]\n[width: 120px]Volume:[/][bold]{asksvolume}[/]"
  })
}));
asksTotalVolume.strokes.template.set("strokeWidth", 2)
asksTotalVolume.fills.template.setAll({
  visible: true,
  fillOpacity: 0.2
});

var bidVolume = chart.series.push(am5xy.ColumnSeries.new(root, {
  minBulletDistance: 10,
  xAxis: xAxis,
  yAxis: yAxis,
  valueYField: "bidsvolume",
  categoryXField: "value",
  fill: am5.color(0x000000)
}));
bidVolume.columns.template.set("fillOpacity", 0.2);

var asksVolume = chart.series.push(am5xy.ColumnSeries.new(root, {
  minBulletDistance: 10,
  xAxis: xAxis,
  yAxis: yAxis,
  valueYField: "asksvolume",
  categoryXField: "value",
  fill: am5.color(0x000000)
}));
asksVolume.columns.template.set("fillOpacity", 0.2);

// Add cursor
// https://www.amcharts.com/docs/v5/charts/xy-chart/cursor/
var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
  xAxis: xAxis
}));
cursor.lineY.set("visible", false);

// Data loader
function loadData() {
  am5.net.load("https://poloniex.com/public?command=returnOrderBook&amp;currencyPair=BTC_ETH&amp;depth=50").then(function(result) {
    var data = am5.JSONParser.parse(result.response);
    parseData(data);
  }).catch(function() {
    // Failed to load
    // Using drop-in data
    parseData({
      "asks" : [ [ "0.07070", 1.0 ], [ "0.07071", 1.654 ], [ "0.07076", 0.61 ], [ "0.07077", 1.2 ], [ "0.07093", 0.584 ], [ "0.07095", 0.005 ], [ "0.07098", 0.01 ], [ "0.07100", 0.653 ], [ "0.07105", 6.0 ], [ "0.07107", 0.002 ], [ "0.07110", 0.022 ], [ "0.07113", 0.001 ], [ "0.07115", 0.001 ], [ "0.07117", 0.001 ], [ "0.07119", 0.001 ], [ "0.07123", 0.001 ], [ "0.07124", 0.002 ], [ "0.07125", 0.001 ], [ "0.07127", 0.001 ], [ "0.07129", 0.001 ], [ "0.07130", 0.001 ], [ "0.07131", 0.001 ], [ "0.07133", 0.001 ], [ "0.07135", 0.002 ], [ "0.07137", 0.001 ], [ "0.07139", 0.001 ], [ "0.07141", 0.001 ], [ "0.07143", 0.001 ], [ "0.07145", 0.001 ], [ "0.07147", 0.004 ], [ "0.07148", 6.311 ], [ "0.07149", 0.001 ], [ "0.07150", 10.03 ], [ "0.07151", 0.001 ], [ "0.07153", 0.001 ], [ "0.07155", 0.001 ], [ "0.07157", 0.001 ], [ "0.07159", 0.001 ], [ "0.07161", 0.001 ], [ "0.07162", 0.238 ], [ "0.07163", 0.001 ], [ "0.07164", 0.584 ], [ "0.07165", 0.541 ], [ "0.07167", 0.001 ], [ "0.07169", 0.001 ], [ "0.07171", 0.001 ], [ "0.07173", 0.001 ], [ "0.07175", 0.017 ], [ "0.07177", 0.001 ], [ "0.07179", 0.001 ] ],
      "bids" : [ [ "0.07060", 1.001 ], [ "0.07059", 1.544 ], [ "0.07056", 0.61 ], [ "0.07053", 0.002 ], [ "0.07048", 1.2 ], [ "0.07040", 0.05 ], [ "0.07031", 0.663 ], [ "0.07024", 0.005 ], [ "0.07020", 5.99 ], [ "0.07010", 0.022 ], [ "0.07006", 0.001 ], [ "0.07005", 0.003 ], [ "0.07000", 1.0 ], [ "0.06993", 0.002 ], [ "0.06990", 6.15 ], [ "0.06989", 0.519 ], [ "0.06986", 0.001 ], [ "0.06983", 0.024 ], [ "0.06980", 0.031 ], [ "0.06978", 0.01 ], [ "0.06977", 0.81 ], [ "0.06975", 0.053 ], [ "0.06970", 0.022 ], [ "0.06967", 0.531 ], [ "0.06962", 0.017 ], [ "0.06955", 0.004 ], [ "0.06953", 0.002 ], [ "0.06951", 0.031 ], [ "0.06950", 10.0 ], [ "0.06933", 0.301 ], [ "0.06932", 0.606 ], [ "0.06931", 0.022 ], [ "0.06929", 0.015 ], [ "0.06924", 2.48 ], [ "0.06923", 0.5 ], [ "0.06922", 0.2 ], [ "0.06921", 0.5 ], [ "0.06918", 0.03 ], [ "0.06915", 0.001 ], [ "0.06912", 0.069 ], [ "0.06911", 0.002 ], [ "0.06905", 0.003 ], [ "0.06900", 20.39 ], [ "0.06899", 0.002 ], [ "0.06897", 0.242 ], [ "0.06886", 0.808 ], [ "0.06880", 0.026 ], [ "0.06872", 1.0 ], [ "0.06868", 0.005 ], [ "0.06862", 0.584 ] ],
      "isFrozen" : "0",
      "postOnly" : "0",
      "seq" : 67767369
    })
  });
}

function parseData(data) {
  var res = [];
  processData(data.bids, "bids", true, res);
  processData(data.asks, "asks", false, res);
  xAxis.data.setAll(res);
  bidsTotalVolume.data.setAll(res);
  asksTotalVolume.data.setAll(res);
  bidVolume.data.setAll(res);
  asksVolume.data.setAll(res);
}

loadData();

setInterval(loadData, 30000);


// Function to process (sort and calculate cummulative volume)
function processData(list, type, desc, res) {

  // Convert to data points
  for(var i = 0; i < list.length; i++) {
    list[i] = {
      value: Number(list[i][0]),
      volume: Number(list[i][1]),
    }
  }

  // Sort list just in case
  list.sort(function(a, b) {
    if (a.value > b.value) {
      return 1;
    }
    else if (a.value < b.value) {
      return -1;
    }
    else {
      return 0;
    }
  });

  // Calculate cummulative volume
  if (desc) {
    for(var i = list.length - 1; i >= 0; i--) {
      if (i < (list.length - 1)) {
        list[i].totalvolume = list[i+1].totalvolume + list[i].volume;
      }
      else {
        list[i].totalvolume = list[i].volume;
      }
      var dp = {};
      dp["value"] = list[i].value;
      dp[type + "volume"] = list[i].volume;
      dp[type + "totalvolume"] = list[i].totalvolume;
      res.unshift(dp);
    }
  }
  else {
    for(var i = 0; i < list.length; i++) {
      if (i > 0) {
        list[i].totalvolume = list[i-1].totalvolume + list[i].volume;
      }
      else {
        list[i].totalvolume = list[i].volume;
      }
      var dp = {};
      dp["value"] = list[i].value;
      dp[type + "volume"] = list[i].volume;
      dp[type + "totalvolume"] = list[i].totalvolume;
      res.push(dp);
    }
  }

}

}); // end am5.ready()
	
	
	
	if ($('#webticker-3').length) {   
			$("#webticker-3").webTicker({
				height:'auto', 
				duplicate:true, 
				startEmpty:false, 
				rssfrequency:5
			});
		}

	
}); // End of use strict





