//[Dashboard chart Javascript]

//Project:  Crypto Admin - Responsive Admin Template





$(function () {

var options = {
          series: [{
          name: 'series1',
          data: [ 20,15,25,25,30,27,33,30,35,32,25,31,20,25,30,27,33,30,20]
          }],
          chart: {
          height: 118,
          type: 'area',
          toolbar: {
            show: false,
            },
            offsetY: 0,
        },
        colors: ["#ffa800"],
        fill: {
          colors: ["#ffa800" ],
          type: "gradient",
          gradient: {
            shade: "light",
            type: "vertical",
            shadeIntensity: 0.4,
            inverseColors: false,
            opacityFrom: 0.7,
            opacityTo: 0.1,
            stops: [0,85,90],
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
        width: [2],
          curve: 'smooth'
        },
        grid: {
          show: false,
          padding: {
            left: -10,
            top: -25,
            right: -0,
          },
        },
        markers: {
            size: 0,
        },
        xaxis: {
          type: 'datetime',
          categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
        },
        legend: {
            show: false,
        },
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm'
          },
        },
        yaxis: {
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false,
          },
          labels: {
            show: false,
            formatter: function (val) {
              return val + "%";
            }
          },
        },
        xaxis: {
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false,
          },
          labels: {
            show: false,
          },
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart-widget1"), options);
        chart.render();









 // ----------Shifts Overview-----//
    var option = {
        labels: ["Payments", "Withdrawal", "Deposit", "Profits", "Transfers"],
        series: [45, 20, 30, 50, 30],
        chart: {
            type: "donut",
            height: 380,
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        stroke: {
            width: 5,
        },
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: "80%",
                    labels: {
                        show: true,
                        name: {
                            offsetY: -10,
                            color: '#00000',
                        },
                        total: {
                            show: true,
                            fontSize: "20px",
                            fontWeight: 600,
                            color:"#000000",
                            formatter: () => "My Balance",
                            label: "$ 1,250",
                        },
                    },
                },
            },
        },
        states: {
            normal: {
                filter: {
                    type: "none",
                },
            },
            hover: {
                filter: {
                    type: "none",
                },
            },
            active: {
                allowMultipleDataPointsSelection: false,
                filter: {
                    type: "none",
                },
            },
        },
        colors: ["#ff9920", "#ff562f", "#0052cc", "#00baff", "#04a08b"],
    };

    var chart = new ApexCharts(
        document.querySelector("#balance-overview"),
        option
    );
    chart.render();













   var options = {
          series: [44, 55, 25, 30, 25],
          chart: {
          type: 'donut',
          width: 400,
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
        };

        var chart = new ApexCharts(document.querySelector("#balance_chart"), options);
        chart.render();


  
 

        var options = {
          series: [44, 55, 25, 30, 25],
          chart: {
          width: 400,
          type: 'donut',
        },
        dataLabels: {
          enabled: false
        },
         labels: ["Payments", "Withdrawal", "Deposit", "Profits", "Transters"],
         colors: ["#de8647", "#00baff", "#ff562f", "#0052cc", "#543cde"],
        responsive: [{
          breakpoint: 280,
          options: {
            
            legend: {
              show: false
            }
          }
        }],
        legend: {
          show: false,
          position: 'right',
          offsetY: 0,
          height: true,
          fontSize: '14px',
          fontWeight: 600,
          markers: {
            size: 7,
          },
          itemMargin: {
              horizontal: 0,
              vertical: 10
          },
        },
        responsive: [
            {
              breakpoint: 1440,
              options: {
                legend: {
                  position: 'bottom',
                  offsetX: -20,
                }
              }
            }
          ]
        };
        

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();






var chartData = generateChartData();
  function generateChartData() {
    var chartData = [];
    var firstDate = new Date( 2024, 3, 12 );
    firstDate.setDate( firstDate.getDate() - 1000 );
    firstDate.setHours( 0, 0, 0, 0 );

    var a = 2000;
   
    for ( var i = 0; i < 1000; i++ ) {
      var newDate = new Date( firstDate );
      newDate.setHours( 0, i, 0, 0 );

      a += Math.round((Math.random()<0.5?1:-1)*Math.random()*10);
      var b = Math.round( Math.random() * 100000000 );

      chartData.push( {
        "date": newDate,
        "value": a,
        "volume": b
      } );
    }
    return chartData;
  }

var chart = AmCharts.makeChart( "chartdiv21", {
  "type": "stock",
  "theme": "light",
  "categoryAxesSettings": {
    "minPeriod": "mm"
  },

  "dataSets": [ {
    "color": "#fbae1c",
    "fieldMappings": [ {
      "fromField": "value",
      "toField": "value"
    }, {
      "fromField": "volume",
      "toField": "volume"
    } ],

    "dataProvider": chartData,
    "categoryField": "date"
  } ],

  "panels": [ {
    "showCategoryAxis": false,
    "title": "Value",
    "percentHeight": 70,

    "stockGraphs": [ {
      "id": "g1",
      "valueField": "value",
      "type": "smoothedLine",
      "lineThickness": 2,
      "bullet": "round"
    } ],


    "stockLegend": {
      "valueTextRegular": " ",
      "markerType": "none"
    }
  }, {
    "title": "Volume",
    "percentHeight": 30,
    "stockGraphs": [ {
      "valueField": "volume",
      "type": "column",
      "cornerRadiusTop": 2,
      "fillAlphas": 1
    } ],

    "stockLegend": {
      "valueTextRegular": " ",
      "markerType": "none"
    }
  } ],

  "chartScrollbarSettings": {
    "graph": "g1",
    "usePeriod": "10mm",
    "position": "top"
  },

  "chartCursorSettings": {
    "valueBalloonsEnabled": true
  },

  "periodSelector": {
    "position": "top",
    "dateFormat": "YYYY-MM-DD JJ:NN",
    "inputFieldWidth": 150,
    "periods": [ {
      "period": "hh",
      "count": 1,
      "label": "1 hour"
    }, {
      "period": "hh",
      "count": 2,
      "label": "2 hours"
    }, {
      "period": "hh",
      "count": 5,
      "selected": true,
      "label": "5 hour"
    }, {
      "period": "hh",
      "count": 12,
      "label": "12 hours"
    }, {
      "period": "MAX",
      "label": "MAX"
    } ]
  },

  "panelsSettings": {
    "usePrefixes": true
  },

  "export": {
    "enabled": true,
    "position": "bottom-right"
  }
} );









   

  
}); // End of use strict




 
zingchart.render({
  id: 'myChart',
  data: chartConfig,
  height: 400,
  width: '100%'
});



