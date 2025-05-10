//[Dashboard chart Javascript]

//Project:  Crypto Admin - Responsive Admin Template


// mic-container-------------------------
$(document).ready(function() {
$(".mic-container").click(function () {
    if($(".circle").hasClass( "active" )){
      $(".circle").removeClass("active");
    } 
    else{
      $(".circle").addClass("active");
    }   
});
});
// mic-container-end-------------------------

$(".testimonial-content").owlCarousel({
  loop: true,
  items: 1,
  margin: 50,
  dots: true,
  nav: false,
  mouseDrag: true,
  autoplay: true,
  autoplayTimeout: 4000,
  smartSpeed: 800
});

$(".activity-content").owlCarousel({
  loop: true,
  items: 1,
  margin: 50,
  dots: true,
  nav: false,
  mouseDrag: true,
  autoplay: true,
  autoplayTimeout: 4000,
  smartSpeed: 800
});


$(function () {

  'use strict';



var dom = document.getElementById('chart7');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

option = {
  tooltip: {
    trigger: 'item'
  },
  legend: {
    top: '5%',
    left: 'center',
    show: false,
  },
  color:['#ffa800', '#ffffff', '#7a7a7a', '#3d3d3d',],
  series: [
    {
      name: 'growth',
      type: 'pie',
      radius: ['80%', '65%'],
      avoidLabelOverlap: false,
      padAngle: 5,
      itemStyle: {
        borderRadius: 10
      },
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: false,
          fontSize: 40,
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: [
        { value: 1048, name: 'Search Engine' },
        { value: 735, name: 'Direct' },
        { value: 580, name: 'Email' },
        { value: 484, name: 'Union Ads' },
      ]
    }
  ]
};

if (option && typeof option === 'object') {
  myChart.setOption(option);
}

window.addEventListener('resize', myChart.resize);

 

  
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



        var options = {
          series: [{
          name: 'Inflation',
          data: [2.3, 3.1, 4.0, 10.1, 4.0, 3.6, 3.2, 2.3, 1.4, 0.8, 0.5, 0.2]
        }],
          chart: {
          height: 350,
          type: 'bar',
          toolbar: {
            show: false
          },
        },
        plotOptions: {
          bar: {
            borderRadius: 10,
            dataLabels: {
              position: 'top', // top, center, bottom
            },
          }
        },
        dataLabels: {
          enabled: false,
          formatter: function (val) {
            return val + "%";
          },
          offsetY: -20,
          style: {
            fontSize: '12px',
            colors: ["#304758"]
          }
        },
        grid: {
          show: false,
        },
        
        xaxis: {
          categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
          position: 'top',
          labels: {
            show: false,
          },
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
          crosshairs: {
            fill: {
              type: 'gradient',
              gradient: {
                colorFrom: '#D8E3F0',
                colorTo: '#BED1E6',
                stops: [0, 100],
                opacityFrom: 0.4,
                opacityTo: 0.5,
              }
            }
          },
          tooltip: {
            enabled: false,
          }
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
          }
        
        },
        // title: {
        //   text: 'Monthly Inflation in Argentina, 2002',
        //   floating: true,
        //   offsetY: 330,
        //   align: 'center',
        //   style: {
        //     color: '#444'
        //   }
        // }
        };

        var chart = new ApexCharts(document.querySelector("#activity-manager-2"), options);
        chart.render();




         var options = {
          series: [{
            data: [{
              x: 'Team A',
              y: [4, 6]
            }, {
              x: 'Team B',
              y: [0, 0]
            }, {
              x: 'Team C',
              y: [3, 7]
            }, {
              x: 'Team D',
              y: [0, 0]
            }, {
              x: 'Team E',
              y: [4, 6]
            }, {
              x: 'Team F',
              y: [0, 0]
            }, {
              x: 'Team G',
              y: [2, 8]
            }, {
              x: 'Team H',
              y: [0, 0]
            }, {
              x: 'Team I',
              y: [3, 6]
            }, {
              x: 'Team J',
              y: [0, 0]
            }]
        },{
            data: [{
              x: 'Team A',
              y: [0, 0]
            }, {
              x: 'Team B',
              y: [4, 6]
            }, {
              x: 'Team C',
              y: [0, 0]
            }, {
              x: 'Team D',
              y: [6, 7]
            }, {
              x: 'Team E',
              y: [0, 0]
            }, {
              x: 'Team F',
              y: [3, 6]
            }, {
              x: 'Team G',
              y: [0, 0]
            }, {
              x: 'Team H',
              y: [3, 7]
            }, {
              x: 'Team I',
              y: [0, 0]
            }, {
              x: 'Team J',
              y: [4, 6]
            }]
        }],
          chart: {
          type: 'rangeBar',
          height: 150,
          toolbar: {
            show: false
          },
        },
        grid: {
          show: false,
        },
        colors: ['#ffa800', '#D1D3E0'],
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '40%',
          }
        },
        legend: {
            show: false,
        },
        xaxis: {
          labels: {
            show: false,
          },
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          },
        },
        yaxis: {
          labels: {
            show: false,
          },
        },
        dataLabels: {
          enabled: false
        }
        };

        var chart = new ApexCharts(document.querySelector("#activity-manager"), options);
        chart.render();




        /* jQueryKnob */

    $(".knob").knob({
      /*change : function (value) {
       //console.log("change : " + value);
       },
       release : function (value) {
       console.log("release : " + value);
       },
       cancel : function () {
       console.log("cancel : " + this.value);
       },*/
      draw: function () {

        // "tron" case
        if (this.$.data('skin') == 'tron') {

          var a = this.angle(this.cv)  // Angle
              , sa = this.startAngle   // Previous start angle
              , sat = this.startAngle  // Start angle
              , ea                     // Previous end angle
              , eat = sat + a          // End angle
              , r = true;

          this.g.lineWidth = this.lineWidth;

          this.o.cursor
          && (sat = eat - 0.3)
          && (eat = eat + 0.3);

          if (this.o.displayPrevious) {
            ea = this.startAngle + this.angle(this.value);
            this.o.cursor
            && (sa = ea - 0.3)
            && (ea = ea + 0.3);
            this.g.beginPath();
            this.g.strokeStyle = this.previousColor;
            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
            this.g.stroke();
          }

          this.g.beginPath();
          this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
          this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
          this.g.stroke();

          this.g.lineWidth = 2;
          this.g.beginPath();
          this.g.strokeStyle = this.o.fgColor;
          this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
          this.g.stroke();

          return false;
        }
      }
    });
    /* END JQUERY KNOB */

    




//SPARKLINE pia CHARTS
  
//INITIALIZE SPARKLINE CHARTS
$(".sparkline").each(function () {
  var $this = $(this);
  $this.sparkline('html', $this.data());
});







         var options = {
          series: [{
          name: 'series1',
          data: [ 20,15,25,25,30,27,33,30,35,32,25,31,20,25,30,27,33,30,20]
          }],
          chart: {
          height: 80,
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



         var options = {
          chart: {
            height: 320,
            type: "radialBar"
          },

          series: [77],
            colors: ['#0052cc'],
          plotOptions: {
            radialBar: {
              hollow: {
                margin: 15,
                size: "60%"
              },
              track: {
                background: '#ff9920',
              },

              dataLabels: {
                showOn: "always",
                name: {
                  offsetY: 10,
                  show: true,
                  color: "#888",
                  fontSize: "20px"
                },
                value: {
                  color: "#111",
                  fontSize: "30px",
                  show: false
                }
              }
            }
          },

          stroke: {
            lineCap: "round",
          },
          labels: ["5.96%"]
        };

        var chart = new ApexCharts(document.querySelector("#balance_chart"), options);

        chart.render();








        }); // End of use strict



