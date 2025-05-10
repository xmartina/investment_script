//[Dashboard chart Javascript]

//Project:	Crypto Admin - Responsive Admin Template


 		
//-----amchart
am4core.ready(function() {

// Themes begin
am4core.useTheme(am4themes_kelly);
// Themes end

var chart = am4core.create("market-btc", am4charts.XYChart);
chart.padding(0, 15, 0, 15);
chart.colors.step = 3;

var data = [];
var price1 = 1000;
var price2 = 2000;
var price3 = 3000;
var quantity = 1000;
for (var i = 15; i < 3000; i++) {
  price1 += Math.round((Math.random() < 0.5 ? 1 : -1) * Math.random() * 100);
  price2 += Math.round((Math.random() < 0.5 ? 1 : -1) * Math.random() * 100);
  price3 += Math.round((Math.random() < 0.5 ? 1 : -1) * Math.random() * 100);

  if (price1 < 100) {
    price1 = 100;
  }

  if (price2 < 100) {
    price2 = 100;
  }

  if (price3 < 100) {
    price3 = 100;
  }    

  quantity += Math.round((Math.random() < 0.5 ? 1 : -1) * Math.random() * 500);

  if (quantity < 0) {
    quantity *= -1;
  }
  data.push({ date: new Date(2000, 0, i), price1: price1, price2:price2, price3:price3, quantity: quantity });
}


chart.data = data;
// the following line makes value axes to be arranged vertically.
chart.leftAxesContainer.layout = "vertical";

// uncomment this line if you want to change order of axes
//chart.bottomAxesContainer.reverseOrder = true;

var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
dateAxis.renderer.grid.template.location = 0;
dateAxis.renderer.ticks.template.length = 8;
dateAxis.renderer.ticks.template.strokeOpacity = 0.1;
dateAxis.renderer.grid.template.disabled = true;
dateAxis.renderer.ticks.template.disabled = false;
dateAxis.renderer.ticks.template.strokeOpacity = 0.2;
dateAxis.renderer.minLabelPosition = 0.01;
dateAxis.renderer.maxLabelPosition = 0.99;
dateAxis.keepSelection = true;

dateAxis.groupData = true;
dateAxis.minZoomCount = 5;

// these two lines makes the axis to be initially zoomed-in
// dateAxis.start = 0.7;
// dateAxis.keepSelection = true;

var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis.tooltip.disabled = true;
valueAxis.zIndex = 1;
valueAxis.renderer.baseGrid.disabled = true;
// height of axis
valueAxis.height = am4core.percent(65);

valueAxis.renderer.gridContainer.background.fill = am4core.color("#000000");
valueAxis.renderer.gridContainer.background.fillOpacity = 0.05;
valueAxis.renderer.inside = true;
valueAxis.renderer.labels.template.verticalCenter = "bottom";
valueAxis.renderer.labels.template.padding(2, 2, 2, 2);

//valueAxis.renderer.maxLabelPosition = 0.95;
valueAxis.renderer.fontSize = "0.8em"

var series1 = chart.series.push(new am4charts.LineSeries());
series1.dataFields.dateX = "date";
series1.dataFields.valueY = "price1";
series1.dataFields.valueYShow = "changePercent";
series1.tooltipText = "{name}: {valueY.changePercent.formatNumber('[#0c0]+#.00|[#c00]#.##|0')}%";
series1.name = "Stock A";
series1.tooltip.getFillFromObject = false;
series1.tooltip.getStrokeFromObject = true;
series1.tooltip.background.fill = am4core.color("#fff");
series1.tooltip.background.strokeWidth = 2;
series1.tooltip.label.fill = series1.stroke;

var series2 = chart.series.push(new am4charts.LineSeries());
series2.dataFields.dateX = "date";
series2.dataFields.valueY = "price2";
series2.dataFields.valueYShow = "changePercent";
series2.tooltipText = "{name}: {valueY.changePercent.formatNumber('[#0c0]+#.00|[#c00]#.##|0')}%";
series2.name = "Stock B";
series2.tooltip.getFillFromObject = false;
series2.tooltip.getStrokeFromObject = true;
series2.tooltip.background.fill = am4core.color("#fff");
series2.tooltip.background.strokeWidth = 2;
series2.tooltip.label.fill = series2.stroke;

var series3 = chart.series.push(new am4charts.LineSeries());
series3.dataFields.dateX = "date";
series3.dataFields.valueY = "price3";
series3.dataFields.valueYShow = "changePercent";
series3.tooltipText = "{name}: {valueY.changePercent.formatNumber('[#0c0]+#.00|[#c00]#.##|0')}%";
series3.name = "Stock C";
series3.tooltip.getFillFromObject = false;
series3.tooltip.getStrokeFromObject = true;
series3.tooltip.background.fill = am4core.color("#fff");
series3.tooltip.background.strokeWidth = 2;
series3.tooltip.label.fill = series3.stroke;

var valueAxis2 = chart.yAxes.push(new am4charts.ValueAxis());
valueAxis2.tooltip.disabled = true;
// height of axis
valueAxis2.height = am4core.percent(35);
valueAxis2.zIndex = 3
// this makes gap between panels
valueAxis2.marginTop = 30;
valueAxis2.renderer.baseGrid.disabled = true;
valueAxis2.renderer.inside = true;
valueAxis2.renderer.labels.template.verticalCenter = "bottom";
valueAxis2.renderer.labels.template.padding(2, 2, 2, 2);
//valueAxis.renderer.maxLabelPosition = 0.95;
valueAxis2.renderer.fontSize = "0.8em";

valueAxis2.renderer.gridContainer.background.fill = am4core.color("#000000");
valueAxis2.renderer.gridContainer.background.fillOpacity = 0.05;

var volumeSeries = chart.series.push(new am4charts.StepLineSeries());
volumeSeries.fillOpacity = 1;
volumeSeries.fill = series1.stroke;
volumeSeries.stroke = series1.stroke;
volumeSeries.dataFields.dateX = "date";
volumeSeries.dataFields.valueY = "quantity";
volumeSeries.yAxis = valueAxis2;
volumeSeries.tooltipText = "Volume: {valueY.value}";
volumeSeries.name = "Series 2";
// volume should be summed
volumeSeries.groupFields.valueY = "sum";
volumeSeries.tooltip.label.fill = volumeSeries.stroke;
chart.cursor = new am4charts.XYCursor();

var scrollbarX = new am4charts.XYChartScrollbar();
scrollbarX.series.push(series1);
scrollbarX.marginBottom = 20;
var sbSeries = scrollbarX.scrollbarChart.series.getIndex(0);
sbSeries.dataFields.valueYShow = undefined;
chart.scrollbarX = scrollbarX;

// Add range selector
var selector = new am4plugins_rangeSelector.DateAxisRangeSelector();
selector.container = document.getElementById("controls");
selector.axis = dateAxis;

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



