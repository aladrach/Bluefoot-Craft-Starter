
/*
 * Author: Adam Randlett
 * adam@randlett.net
 *
 *
*/

EVRP.mdl = (function ($, mdl, window, document, undefined){

  var _body = $('body');
  mdl.Modal = {};

  mdl.Modal.init = function(){
    //mdl.log("EVRP.MODAL.INIT --");

    //get Modal html from Events_AjaxController.php
    //append to overlay we already added to document
    Craft.postActionRequest('venti/ajax/modal', {}, function(data){
      _body.append(data);
      mdl.Modal.mod = $("[data-modal]");
      mdl.Modal.overlay = $("[data-events-overlay]");
      mdl.Modal.events();
    });

  };




  /* --------------------------------------------- *\
        EVENT SETUP.
  \*  -------------------------------------------- */

  mdl.Modal.events = function(){

    _body.on('click','[data-events-click],[data-events-edit]',function(e){
      var $this = $(this);

      if($this.is("input")){
        if($this.prop("checked")){
          mdl.Modal.open();
          mdl.Widget.populate();
        }
        //If there is a repeat event values in hidden fields, clear all.
        if($('[data-rrule]').attr("value") !== ""){
          mdl.Widget.clearSummary();
        }
      }
      if($this.is("a")){
        mdl.Modal.open();
        mdl.Widget.populate();
        e.preventDefault();
      }
    });

    _body.on('click','.evrp_modal_close',function(){
      mdl.Modal.close();
      /* If no previous repeat event then uncheck
         if there is a repeat event we need to leave
         it check the user just canceled the window. */
      if($('[data-rrule]').attr("value") === ""){
        $('[data-events-click]').removeAttr("checked");
      }
    });

  };





  /* --------------------------------------------- *\
        MODEL OPEN.
  \*  -------------------------------------------- */

   mdl.Modal.open = function(){
    mdl.Modal.overlay.fadeIn('fast');
    //sets widget starts on input
    mdl.Widget.setStartsOn();
  };




  /* --------------------------------------------- *\
      MODAL CLOSE.
  \*  -------------------------------------------- */

  mdl.Modal.close = function(){
    mdl.Modal.overlay.fadeOut('fast');
  };


  mdl.Modal.init();
  return mdl;

}($, EVRP || {}, this, this.document));