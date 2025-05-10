//[Dashboard chart Javascript]

//Project:	Crypto Admin - Responsive Admin Template


 		
//-----amchart

am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_animated);
// Themes end

var chart = am4core.create("market-btc", am4charts.XYChart);
chart.paddingRight = 20;

chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";

var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.grid.template.location = 0;

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.tooltip.disabled = true;

var series = chart.series.push(new am4charts.CandlestickSeries());
series.dataFields.dateX = "date";
series.dataFields.valueY = "close";
series.dataFields.openValueY = "open";
series.dataFields.lowValueY = "low";
series.dataFields.highValueY = "high";
series.simplifiedProcessing = true;
series.tooltipText = "Open:${openValueY.value}\nLow:${lowValueY.value}\nHigh:${highValueY.value}\nClose:${valueY.value}";

chart.cursor = new am4charts.XYCursor();

// a separate series for scrollbar
var lineSeries = chart.series.push(new am4charts.LineSeries());
lineSeries.dataFields.dateX = "date";
lineSeries.dataFields.valueY = "close";
// need to set on default state, as initially series is "show"
lineSeries.defaultState.properties.visible = false;

// hide from legend too (in case there is one)
lineSeries.hiddenInLegend = true;
lineSeries.fillOpacity = 0.5;
lineSeries.strokeOpacity = 0.5;

var scrollbarX = new am4charts.XYChartScrollbar();
scrollbarX.series.push(lineSeries);
chart.scrollbarX = scrollbarX;

chart.data = [ {
    "date": "2011-08-01",
    "open": "136.65",
    "high": "136.96",
    "low": "134.15",
    "close": "136.49"
  }, {
    "date": "2011-08-02",
    "open": "135.26",
    "high": "135.95",
    "low": "131.50",
    "close": "131.85"
  }, {
    "date": "2011-08-05",
    "open": "132.90",
    "high": "135.27",
    "low": "128.30",
    "close": "135.25"
  }, {
    "date": "2011-08-06",
    "open": "134.94",
    "high": "137.24",
    "low": "132.63",
    "close": "135.03"
  }, {
    "date": "2011-08-07",
    "open": "136.76",
    "high": "136.86",
    "low": "132.00",
    "close": "134.01"
  }, {
    "date": "2011-08-08",
    "open": "131.11",
    "high": "133.00",
    "low": "125.09",
    "close": "126.39"
  }, {
    "date": "2011-08-09",
    "open": "123.12",
    "high": "127.75",
    "low": "120.30",
    "close": "125.00"
  }, {
    "date": "2011-08-12",
    "open": "128.32",
    "high": "129.35",
    "low": "126.50",
    "close": "127.79"
  }, {
    "date": "2011-08-13",
    "open": "128.29",
    "high": "128.30",
    "low": "123.71",
    "close": "124.03"
  }, {
    "date": "2011-08-14",
    "open": "122.74",
    "high": "124.86",
    "low": "119.65",
    "close": "119.90"
  }, {
    "date": "2011-08-15",
    "open": "117.01",
    "high": "118.50",
    "low": "111.62",
    "close": "117.05"
  }, {
    "date": "2011-08-16",
    "open": "122.01",
    "high": "123.50",
    "low": "119.82",
    "close": "122.06"
  }, {
    "date": "2011-08-19",
    "open": "123.96",
    "high": "124.50",
    "low": "120.50",
    "close": "122.22"
  }, {
    "date": "2011-08-20",
    "open": "122.21",
    "high": "128.96",
    "low": "121.00",
    "close": "127.57"
  }, {
    "date": "2011-08-21",
    "open": "131.22",
    "high": "132.75",
    "low": "130.33",
    "close": "132.51"
  }, {
    "date": "2011-08-22",
    "open": "133.09",
    "high": "133.34",
    "low": "129.76",
    "close": "131.07"
  }, {
    "date": "2011-08-23",
    "open": "130.53",
    "high": "135.37",
    "low": "129.81",
    "close": "135.30"
  }, {
    "date": "2011-08-26",
    "open": "133.39",
    "high": "134.66",
    "low": "132.10",
    "close": "132.25"
  }, {
    "date": "2011-08-27",
    "open": "130.99",
    "high": "132.41",
    "low": "126.63",
    "close": "126.82"
  }, {
    "date": "2011-08-28",
    "open": "129.88",
    "high": "134.18",
    "low": "129.54",
    "close": "134.08"
  }, {
    "date": "2011-08-29",
    "open": "132.67",
    "high": "138.25",
    "low": "132.30",
    "close": "136.25"
  }, {
    "date": "2011-08-30",
    "open": "139.49",
    "high": "139.65",
    "low": "137.41",
    "close": "138.48"
  }, {
    "date": "2011-09-03",
    "open": "139.94",
    "high": "145.73",
    "low": "139.84",
    "close": "144.16"
  }, {
    "date": "2011-09-04",
    "open": "144.97",
    "high": "145.84",
    "low": "136.10",
    "close": "136.76"
  }, {
    "date": "2011-09-05",
    "open": "135.56",
    "high": "137.57",
    "low": "132.71",
    "close": "135.01"
  }, {
    "date": "2011-09-06",
    "open": "132.01",
    "high": "132.30",
    "low": "130.00",
    "close": "131.77"
  }, {
    "date": "2011-09-09",
    "open": "136.99",
    "high": "138.04",
    "low": "133.95",
    "close": "136.71"
  }, {
    "date": "2011-09-10",
    "open": "137.90",
    "high": "138.30",
    "low": "133.75",
    "close": "135.49"
  }, {
    "date": "2011-09-11",
    "open": "135.99",
    "high": "139.40",
    "low": "135.75",
    "close": "136.85"
  }, {
    "date": "2011-09-12",
    "open": "138.83",
    "high": "139.00",
    "low": "136.65",
    "close": "137.20"
  }, {
    "date": "2011-09-13",
    "open": "136.57",
    "high": "138.98",
    "low": "136.20",
    "close": "138.81"
  }, {
    "date": "2011-09-16",
    "open": "138.99",
    "high": "140.59",
    "low": "137.60",
    "close": "138.41"
  }, {
    "date": "2011-09-17",
    "open": "139.06",
    "high": "142.85",
    "low": "137.83",
    "close": "140.92"
  }, {
    "date": "2011-09-18",
    "open": "143.02",
    "high": "143.16",
    "low": "139.40",
    "close": "140.77"
  }, {
    "date": "2011-09-19",
    "open": "140.15",
    "high": "141.79",
    "low": "139.32",
    "close": "140.31"
  }, {
    "date": "2011-09-20",
    "open": "141.14",
    "high": "144.65",
    "low": "140.31",
    "close": "144.15"
  }, {
    "date": "2011-09-23",
    "open": "146.73",
    "high": "149.85",
    "low": "146.65",
    "close": "148.28"
  }, {
    "date": "2011-09-24",
    "open": "146.84",
    "high": "153.22",
    "low": "146.82",
    "close": "153.18"
  }, {
    "date": "2011-09-25",
    "open": "154.47",
    "high": "155.00",
    "low": "151.25",
    "close": "152.77"
  }, {
    "date": "2011-09-26",
    "open": "153.77",
    "high": "154.52",
    "low": "152.32",
    "close": "154.50"
  }, {
    "date": "2011-09-27",
    "open": "153.44",
    "high": "154.60",
    "low": "152.75",
    "close": "153.47"
  }, {
    "date": "2011-09-30",
    "open": "154.63",
    "high": "157.41",
    "low": "152.93",
    "close": "156.34"
  }, {
    "date": "2011-10-01",
    "open": "156.55",
    "high": "158.59",
    "low": "155.89",
    "close": "158.45"
  }, {
    "date": "2011-10-02",
    "open": "157.78",
    "high": "159.18",
    "low": "157.01",
    "close": "157.92"
  }, {
    "date": "2011-10-03",
    "open": "158.00",
    "high": "158.08",
    "low": "153.50",
    "close": "156.24"
  }, {
    "date": "2011-10-04",
    "open": "158.37",
    "high": "161.58",
    "low": "157.70",
    "close": "161.45"
  }, {
    "date": "2011-10-07",
    "open": "163.49",
    "high": "167.91",
    "low": "162.97",
    "close": "167.91"
  }, {
    "date": "2011-10-08",
    "open": "170.20",
    "high": "171.11",
    "low": "166.68",
    "close": "167.86"
  }, {
    "date": "2011-10-09",
    "open": "167.55",
    "high": "167.88",
    "low": "165.60",
    "close": "166.79"
  }, {
    "date": "2011-10-10",
    "open": "169.49",
    "high": "171.88",
    "low": "153.21",
    "close": "162.23"
  }, {
    "date": "2011-10-11",
    "open": "163.01",
    "high": "167.28",
    "low": "161.80",
    "close": "167.25"
  }, {
    "date": "2011-10-14",
    "open": "167.98",
    "high": "169.57",
    "low": "163.50",
    "close": "166.98"
  }, {
    "date": "2011-10-15",
    "open": "165.54",
    "high": "170.18",
    "low": "165.15",
    "close": "169.58"
  }, {
    "date": "2011-10-16",
    "open": "172.69",
    "high": "173.04",
    "low": "169.18",
    "close": "172.75"
  }];

}); // end am4core.ready()




  
am5.ready(function() {

// Create root element
// https://www.amcharts.com/docs/v5/getting-started/#Root_element
var root = am5.Root.new("market-depth");

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





