//[Dashboard Javascript]

//Project:  Crypto Admin - Responsive Admin Template
//Primary use:   Used only for the  widget inline charts


$(function () {

  'use strict';
    
        
    var options = {
          chart: {
            height: 325,
            type: "radialBar"
          },

          series: [77],
            colors: ['#0052cc'],
          plotOptions: {
            radialBar: {
              hollow: {
                margin: 15,
                size: "70%"
              },
              track: {
                background: '#ff9920',
              },

              dataLabels: {
                showOn: "always",
                name: {
                  offsetY: -10,
                  show: false,
                  color: "#888",
                  fontSize: "13px"
                },
                value: {
                  color: "#111",
                  fontSize: "30px",
                  show: true
                }
              }
            }
          },

          stroke: {
            lineCap: "round",
          },
          labels: ["Progress"]
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);

        chart.render();

        


         var options = {
          series: [{
          name: 'XYZ MOTORS',
          data: dates
        }],
          chart: {
          type: 'area',
          stacked: false,
          height: 350,
          zoom: {
            type: 'x',
            enabled: true,
            autoScaleYaxis: true
          },
          toolbar: {
            autoSelected: 'zoom'
          }
        },
        dataLabels: {
          enabled: false
        },
        markers: {
          size: 0,
        },
        title: {
          text: 'Stock Price Movement',
          align: 'left'
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.5,
            opacityTo: 0,
            stops: [0, 90, 100]
          },
        },
        yaxis: {
          labels: {
            formatter: function (val) {
              return (val / 1000000).toFixed(0);
            },
          },
          title: {
            text: 'Price'
          },
        },
        xaxis: {
          type: 'datetime',
        },
        tooltip: {
          shared: false,
          y: {
            formatter: function (val) {
              return (val / 1000000).toFixed(0)
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#revenue5"), options);
        chart.render();
    
    
}); // End of use strict