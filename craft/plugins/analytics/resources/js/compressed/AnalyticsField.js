AnalyticsField=Garnish.Base.extend({init:function(e){"undefined"==typeof google.visualization?("undefined"==typeof AnalyticsChartLanguage&&(AnalyticsChartLanguage="en"),google.load("visualization","1",{packages:["corechart"],language:AnalyticsChartLanguage,callback:$.proxy(this,"initField",e)})):this.initField(e)},initField:function(e){this.$element=$("#"+e),this.$field=$(".analytics-field",this.$element),this.$spinner=$(".spinner",this.$element),this.$errorElement=$(".error",this.$element),this.$metricElement=$(".analytics-metric select",this.$element),this.$chartElement=$(".chart",this.$element),this.elementId=$(".analytics-field",this.$element).data("element-id"),this.locale=$(".analytics-field",this.$element).data("locale"),this.chart=!1,this.chartData=!1,this.addListener(Garnish.$win,"resize","resize"),this.addListener(this.$metricElement,"change","onMetricChange"),this.$metricElement.trigger("change")},onMetricChange:function(e){this.$metric=$(e.currentTarget).val(),this.request()},request:function(){this.chartData=new google.visualization.DataTable,this.$spinner.removeClass("hidden"),Craft.postActionRequest("analytics/explorer/elementReport",{elementId:this.elementId,locale:this.locale,metric:this.$metric},$.proxy(function(e){if(this.$spinner.addClass("hidden"),"undefined"!=typeof e.error)this.$errorElement.html(e.error),this.$field.addClass("analytics-error");else{var t=e.data;this.$field.removeClass("analytics-error"),$.each(t.columns,$.proxy(function(e,t){var i=AnalyticsUtils.parseColumn(t);this.chartData.addColumn(i.type,i.label)},this)),rows=AnalyticsUtils.parseRows(t.columns,t.rows),this.chartData.addRows(rows),this.chart=new google.visualization.AreaChart(this.$chartElement.get(0)),"undefined"!=typeof this.chart&&this.chart.draw(this.chartData,this.chartOptions)}},this))},resize:function(){this.chart&&this.chart.draw(this.chartData,this.chartOptions)},chartOptions:{colors:["#058DC7"],backgroundColor:"#fdfdfd",areaOpacity:.1,pointSize:8,lineWidth:4,legend:!1,hAxis:{textStyle:{color:"#888"},baselineColor:"#fdfdfd",gridlines:{color:"none"}},vAxis:{maxValue:5},series:{0:{targetAxisIndex:0},1:{targetAxisIndex:1}},vAxes:[{textStyle:{color:"#888"},format:"#",textPosition:"in",baselineColor:"#eee",gridlines:{color:"#eee"}},{textStyle:{color:"#888"},format:"#",textPosition:"in",baselineColor:"#eee",gridlines:{color:"#eee"}}],chartArea:{top:10,bottom:10,width:"100%",height:"80%"}}});