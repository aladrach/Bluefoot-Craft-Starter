
/*
 * Author: Adam Randlett
 * adam@randlett.net
 *
 *
*/

EVRP.erw = (function ($, erw, window, document, undefined){

  var _body = $('body');
  erw.Widget = {};

  erw.Widget.labels = {
    "everytext":[
      "days",
      "weeks",
      "months",
      "years"
    ]
  }



  erw.Widget.init = function(){
    var ruleInp = $("[data-rrule]");
    erw.Widget.events();
    // if(ruleInp.val() != ""){
    //   erw.Widget.populate(ruleInp);
    // }
  };



  /* --------------------------------------------- *\
      START DATE METHODS.
  \*  -------------------------------------------- */

  // Sets the Start On day from the event Start Date
  erw.Widget.setStartsOn = function () {
    $("#er_starts").attr("value",$("#fields-eventStartDate-date").val());
  };


  erw.Widget.getStartDate = function () {
    var sdate = document.querySelector("#fields-eventStartDate-date").value,
        time = erw.Widget.convert12to24(document.querySelector("#fields-eventStartDate-time").value),
        timeArry = time.split(':'),
        dateArry = sdate.split('/'),
        start = new Date(Date.UTC(dateArry[2],dateArry[0]-1,dateArry[1],timeArry[0],timeArry[1],"00"));

    // If no start date use today formatted M/D/YYYY
    if(start === ""){
      var date = new Date();
      start = erw.formatDate(date);
    }

    return start.toUTCString();
  };


  erw.Widget.convert12to24 = function (timeStr) {
      var meridian = timeStr.substr(timeStr.length-2).toLowerCase();;
      var hours =  timeStr.substr(0, timeStr.indexOf(':'));
      var minutes = timeStr.substring(timeStr.indexOf(':')+1, timeStr.indexOf(' '));
      if (meridian=='pm')
      {
          if (hours!=12)
          {
              hours=hours*1+12;
          }
          else
          {
              hours = (minutes!='00') ? '0' : '24' ;
          }
      }

      return hours+':'+minutes;
  }

  //Format Date 08/14/2014
  erw.Widget.formatDate = function (date) {
    var day = date.getDate(),
        month = date.getMonth() + 1,
        year = date.getFullYear();
    return start = month + "/" + day + "/" + year;
  };

  // erw.Widget.formatDateFromISO = function (date) {
  //   return date.getMonth() + "/" + date.getDate() + "/" + date.getFullYear();
  // }



  /* --------------------------------------------- *\
        EVENTS.
  \*  -------------------------------------------- */

  erw.Widget.events = function () {

    // Done button click
    _body.on("click","#evrp_done", function () {
      erw.Widget.done();
    });

    // Ends On Radio Set click
    _body.on("click",".evrp_modal [type=radio], .evrp_modal [type=checkbox]", function () {
      var $this = $(this);
      if($this.is('[name=endson]')){
        erw.Widget.repeatEnds($this);
      }
      erw.Widget.serial();
    });

    _body.on("change",".evrp_modal select, .evrp_modal input", function () {
      var $this = $(this);
      //Ends Radio Buttons and Associated Inputs

      if($this.is('#er_frequency')){
        var value = $this.find("option:selected").val();
        erw.Widget.frequencyToggle(value);
      }
      erw.Widget.serial();
    });


    _body.on('click','#modtabs a',erw.Widget.tabs);

    /* 
     * On Exclude Date change highlight add button.
     */ 
    _body.on('change keyup','#er_exclude-date',function(){
        var addDate = $("#er_adddate");
        if ($(this).val() != "") {
          addDate.addClass('ready');
        }else{
          addDate.removeClass('ready');
        }
    });
    _body.on('click','#er_adddate',function () {
      var exdate = $('#er_exclude-date'),
          date = exdate.val(),
          temp = $("#er_exc_element").text(),
          output = $("#er_exc_elements"),
          element = $(temp);

      if(date.trim() != ""){
        element.find("input").attr("value",date);
        element.find(".title").append(date);
        output.append(element);
        exdate.val('');
        $(this).removeClass('ready');
      }
    });

    _body.on('click','#er_exc_elements .delete',function () {
      var $this = $(this);
          $this.parent().fadeOut( function (){
            $(this).remove();
          });
    });

  };



  erw.Widget.tabs = function(e){
    var $this = $(this),
        id = $this.attr("href");

        $("#modtabs").find(".sel").removeClass('sel');
        $this.addClass('sel');
        $(id).siblings().hide();
        $(id).show();
        e.preventDefault();
  };


  /* --------------------------------------------- *\
        UI CHANGES.
  \*  -------------------------------------------- */

  erw.Widget.frequencyToggle = function(value){

    switch(parseInt(value)) {
      case 0: //days
        erw.Widget.uiChange(0,false,true,false);
        break;
      case 1: //every weekday
        erw.Widget.uiChange("none",false,false,false);
        break;
      case 2: //every M W F
        erw.Widget.uiChange("none",false,false,false);
        break;
      case 3: //every T TH
        erw.Widget.uiChange("none",false,false,false);
        break;
      case 4: //weekly
        erw.Widget.uiChange(1,true,true,false);
        break;
      case 5: //monthly
         erw.Widget.uiChange(2,false,true,true);
        break;
      case 6: //yearly
         erw.Widget.uiChange(3,false,true,false);
        break;
    }
  };

  erw.Widget.uiChange = function(textid,ro,re,rb){
    if(erw.Helpers.is_int(textid)){
      erw.Widget.setRepeatEveryText(textid);
    }
    if(ro){
      erw.Widget.showRepeatsOn();
    }else{
      erw.Widget.hideRepeatsOn();
    }

    if(re){
      erw.Widget.showRepeatEvery();
    }else{
      erw.Widget.hideRepeatEvery();
    }

    if(rb){
      erw.Widget.showRepeatBy();
    }else{
      erw.Widget.hideRepeatBy();
    }
  }

  erw.Widget.setRepeatEveryText = function(value){
    $('#er_every').find("label").html(erw.Widget.labels['everytext'][value]);
  };

  erw.Widget.hideRepeatEvery = function(){ $("#er_every").hide(); };
  erw.Widget.showRepeatEvery = function(){ $("#er_every").show(); };

  erw.Widget.hideRepeatsOn = function(){ $("#er_dow").hide(); };
  erw.Widget.showRepeatsOn = function(){ $("#er_dow").show(); };

  erw.Widget.hideRepeatBy = function(){ $("#er_repeatby").hide(); };
  erw.Widget.showRepeatBy = function(){ $("#er_repeatby").show(); };


  erw.Widget.repeatEnds = function(elm){
     var $this = $(elm);

      if($this.is("#er_endsnever")){
        $("#er_endson_rdio").next('label').find("input").attr("disabled","disabled");
        $("#er_endsafter").next('label').find("input").attr("disabled","disabled");
      }

      if($this.is("#er_endsafter")){
        $this.next("label").find('input').removeAttr('disabled');
        $("#er_endson_rdio").next('label').find("input").attr("disabled","disabled");
      }

      if($this.is("#er_endson_rdio")){
        $this.next("label").find('input').removeAttr('disabled');
        $("#er_endsafter").next('label').find("input").attr("disabled","disabled");
      }
  };



  /* --------------------------------------------- *\
        SUMMARY TEXT & HIDDEN INPUTS.
  \*  -------------------------------------------- */

  erw.Widget.setSummary = function(txt){
    $(".evrp_summary,.rrule_human_text").html(txt);
    $("[data-summary]").attr("value",txt);
    if($("[data-events-edit]:hidden")){
      $("[data-events-edit]:hidden").show();
    }
  };

  erw.Widget.clearSummary = function(){
    // clears rRule & summary hidden input as well as text holders next
    // too repeat checkbox and event modal summary box.
    $(".evrp_summary,.rrule_human_text").html("");
    $("[data-summary],[data-rrule]").attr("value","");
    $("#fields-rRule").attr("data-rule-string","");

    if($("[data-events-edit]:visible")){
      $("[data-events-edit]:visible").hide();
    }

  };




  /* --------------------------------------------- *\
        POPULATE WIDGET IF SET.
  \*  -------------------------------------------- */

  erw.Widget.populate = function(){
    var rruleHiddenInput = $('[data-rrule]'),
        imap = erw.Widget.inputMap();
    if(rruleHiddenInput.val() !== ""){
      var opt = erw.getRuleParams(rruleHiddenInput.val());
      //console.info(opt);
      //document.querySelectorAll('#er_frequency option').selected=false;
      $(".evrp_summary").html($(".rrule_human_text").html());
      
      if(opt['FREQ'] === "DAILY"){
        //document.querySelectorAll('#er_frequency option').selected=false;
        document.querySelector('#er_frequency [value="0"]').selected=true;
      }

      if(opt['FREQ'] === "WEEKLY"){
        //document.querySelectorAll('#er_frequency option').selected=false;
        if(erw.objectsAreSame(opt['BYDAY'],["MO","TU","WE","TH","FR"])){
          document.querySelector('#er_frequency [value="1"]').selected=true;
          erw.Widget.hideRepeatEvery();

        }else{
 
          if(erw.objectsAreSame(opt['BYDAY'],["MO","WE","FR"])){
            document.querySelector('#er_frequency [value="2"]').selected=true;
            erw.Widget.hideRepeatEvery();

          }else{
            
            if(erw.objectsAreSame(opt['BYDAY'],["TU","TH"])){
              document.querySelector('#er_frequency [value="3"]').selected=true;
              erw.Widget.hideRepeatEvery();

            }else{

              document.querySelector('#er_frequency [value="4"]').selected=true;
              erw.Widget.showRepeatsOn();
              $("#er_every").find("label").html("weeks");
          
              if($.isArray(opt['BYDAY'])){
                imap['dow'].map(function(t){
                  if($.inArray($(this).attr("name"),opt['BYDAY']) !== -1){
                    $(this).attr("checked","checked");
                  }
                });
              }else{
                $("#er_dow").find("input[name="+opt['BYDAY']+"]").attr("checked",true);
              }
            }
          }
        }
      }

      if(opt['FREQ'] === "MONTHLY"){
        document.querySelector('#er_frequency [value="5"]').selected=true;
        //$(imap["repeats"]).find("option:eq(5)").attr("selected",true);
        $("#er_every").find("label").html("months");
        erw.Widget.hideRepeatsOn();
        erw.Widget.showRepeatBy();

        if(opt['BYMONTHDAY']){
          imap["repeatsby"].eq("0").attr("checked","checked");
        }
        if(opt['BYDAY']){
          imap["repeatsby"].eq("1").attr("checked","checked");
        }
      }

      if(opt['FREQ'] === "YEARLY"){
        document.querySelector('#er_frequency [value="6"]').selected=true;
        //$(imap["repeats"]).find("option:eq(6)").attr("selected","selected");
        $("#er_every").find("label").html("years");
        erw.Widget.hideRepeatsOn();
        erw.Widget.hideRepeatBy();
      }


      if(opt['INTERVAL']){
        imap["repeatsevery"].find("option[value="+opt['INTERVAL']+"]").attr("selected","selected");
      }

      if(opt['COUNT']){
        imap["occur"].attr("value",opt['COUNT']).removeAttr("disabled");
        imap["endson"].eq(1).attr("checked","checked");
      }

      if(opt['UNTIL']){
        imap["endson"].eq(2).attr("checked","checked");
        var date = erw.stringToDate(opt['UNTIL']),
            dateText = (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
        imap["endsondate"].attr("value",dateText);
      }

      if(opt['EXDATE']){
       
        var dates = [],
            temp = $("#er_exc_element").text(),
            output = $("#er_exc_elements");
            
        if(output.find('div').length < 1){

            // If EXDATE has more than one item "," will be present
            if(opt['EXDATE'].indexOf(',') !== -1){

              var dateArray = opt['EXDATE'].split(',');
              for(var i = 0; i< dateArray.length; i++){
                var newDate = erw.strToDateUTC(dateArray[i]);
                dates.push(newDate);
              };

            }else{
              
              var newDate = erw.strToDateUTC(opt['EXDATE']);
              dates.push(newDate);
            }
            
            for(var i = 0; i< dates.length; i++){
              var element = $(temp);
              element.find("input").attr("value",dates[i]);
              element.find(".title").append(dates[i]);
              output.append(element);
            }        
          }
        }

    }
  };



// Convert date back to original format (8/25/2014) from 20140825T000000Z
 erw.strToDateUTC = function(date){
    var re = /^(\d{4})(\d{2})(\d{2})(T(\d{2})(\d{2})(\d{2})Z?)?$/;
      var bits = re.exec(date),
          year = "",
          month = "",
          day = "";
      if (!bits) {
          throw new Error('Invalid DATE value: ' + date)
      }
      year = bits[1];

      if(bits[2].charAt(0) === "0"){
        month = bits[2].replace("0","");
      }else{
        month = bits[2];
      }
      if(bits[3].charAt(0) === "0"){
        day = bits[3].replace("0","");
      }else{
        day = bits[3];
      }
      var datetxt = month + "/" + day + "/" + year;
      return datetxt;
 };




 erw.stringToDate = function(until){
  //barrowed from RRULE.js to reconvert datestring: Dateutil.untilStringToDate
  var re = /^(\d{4})(\d{2})(\d{2})(T(\d{2})(\d{2})(\d{2})Z)?$/;
      var bits = re.exec(until);
      if (!bits) {
          throw new Error('Invalid UNTIL value: ' + until)
      }
      return new Date(
          Date.UTC(
            bits[1],
            bits[2] - 1,
            bits[3],
            bits[5] || 0,
            bits[6] || 0,
            bits[7] || 0
          )
      );
 }





 /* --------------------------------------------- *\
       SERIALIZE.
 \*  -------------------------------------------- */

 erw.Widget.serial = function () {
   var imap = erw.Widget.inputMap();
       dataMap = {};


   dataMap["repeats"] = parseInt($(imap["repeats"]).find("option:selected").val());

   //if dataMap["repeats"] has a value of 0,4,5,6 
   // the Repeat Every select is being used so we need to get the value.
   if($.inArray(dataMap["repeats"],[0,4,5,6]) !== -1){
     dataMap["repeatsevery"]  = imap["repeatsevery"].find("option:selected").val(); //$("#er_every_select:visible")
   }

   //if dataMap["repeats"] has a value of 4
   // the repeats on input is being used so we need to get the value. 
   if(dataMap["repeats"] === 4){
     dataMap["repeatson"]     = imap["repeatson"].map(function (t) { return $(this).attr("name"); });  //$("input[id*=er_dow]:checked:visible")
   }
   dataMap["endson"]          = imap["endson"].filter(":checked").val(); //$("[name=endson]:checked")
   dataMap["occur"]           = imap["occur"].not(':disabled').val(); //$("#er_occurrences:not(:disabled)")
   dataMap["endsondate"]      = imap["endsondate"].not(':disabled').val(); //$("#er_endson-date:not(:disabled)").val();

   //if dataMap["repeats"] has a value of 5
   // the repeats by input is being used so we need to get the value.
   if(dataMap["repeats"] === 5){
     dataMap["repeatsby"]     = $(imap["repeatsby"]).filter(":checked").val(); //$("input[name=repeatby]:checked:visible").val();
   }
   dataMap["startdate"]       = erw.Widget.getStartDate();

   return dataMap;

 };



  erw.Widget.inputMap = function(){
      var iMap = {};
      iMap["repeats"]       = "#er_frequency"; //$("#er_frequency");
      iMap["repeatsevery"]  = $("#er_every_select");
      iMap["repeatson"]     = $("#er_dow").find('input:checked');
      iMap["dow"]           = $("#er_dow").find('input');
      iMap["endson"]        = $("[name=endson]");
      iMap["occur"]         = $("#er_occurrences");
      iMap["endsondate"]    = $("#er_endson-date");
      iMap["repeatsby"]     = $("input[name=repeatby]");
      return iMap;
  }





  /* --------------------------------------------- *\
        EXCLUDE DATES.
  \*  -------------------------------------------- */

  erw.Widget.excludeDates = function(){
    var inputs = $("#er_exc_elements").find("input"),
        values = [];
      inputs.each(function(){
        var value = $(this).val(),
            valueSplit = value.split("/"),
            year = valueSplit[2],
            month = parseInt(valueSplit[0]) > 9 ? valueSplit[0] : "0" + valueSplit[0],
            day = parseInt(valueSplit[1]) > 9 ? valueSplit[1] : "0" + valueSplit[1],
            date = year+month+day;
        values.push(date);
      });

      return values.join(",");
  };





  /* --------------------------------------------- *\
        DONE.
  \*  -------------------------------------------- */

  erw.Widget.done = function(){
    var excludedDates = erw.Widget.excludeDates();
    erw.RRule.getRuleString(erw.Widget.serial(),function(response){
      erw.Widget.saveRuleOptions(response,excludedDates);
    });
  };



  erw.Widget.saveRuleOptions = function(response,excludedDates){
    if(response){
        var ruleString = response,
        ruleOptions = ruleOptions = erw.RRule.getRRuleOptions(ruleString),
        exdate = excludedDates !="" ? ";EXDATE=" + excludedDates : "";
        $("[data-rrule]").attr("value",ruleOptions + exdate);
        erw.Widget.setSummary(ruleString);
        erw.Modal.close();

        // erw.RRule.getRRuleInstances(ruleOptions,function(instances){
        //   erw.Widget.setSummary(ruleString);
        //   erw.Ajax.postActionRequest('ruleOptions', {rrule:ruleOptions + exdate, startdate:erw.Widget.getStartDate()/*, dates: instances*/}, function(data){
        //     //console.log(data);
        //     erw.Modal.close();
        //   });
        // });

      }
  };


  erw.objectsAreSame = function(x, y) {
     var objectsAreSame = true;
     for(var propertyName in x) {
        if(x[propertyName] !== y[propertyName]) {
           objectsAreSame = false;
           break;
        }
     }
     return objectsAreSame;
  }


  erw.Widget.init();
  return erw;

}($, EVRP || {}, this, this.document));