<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

/**
 *  A allows you to select a lat/lng and zoom option
 *  At the moment this requires 3 existing elements (hidden or text fields to work)
 * EG:
 * <code>
 *   $form = Tk_Form::create('City', $this->object);
 *   $form->addDefaultEvents($this->getCrumbUrl());
 *   $form->addField(Tk_Form_Field_Text::create('mapLat'), Tk_Form_Type_Float::create())->setWidth(70);
 *   $form->addField(Tk_Form_Field_Text::create('mapLng'), Tk_Form_Type_Float::create())->setWidth(70);
 *   $form->addField(Tk_Form_Field_Text::create('mapZoom'), Tk_Form_Type_Integer::create())->setWidth(40)->setMaxlength(2);
 *   ...
 * </code>
 *
 *
 * NOTE: Currently not tab enabled, so this field must be shown on page-load, not a hidden div.
 *
 * @package Form
 */
class Form_Field_GmapSelect extends Form_Field
{
    
    
    
    
    /**
     * Create an instance of this object
     *
     * @param string $name
     * @return Form_Field_Text
     */
    static function create($name)
    {
        $obj = new self($name, Form_Type_String::create());
        $obj->setWidth(500);
        $obj->setHeight(350);
        return $obj;
    }
    
    
    
    
    
    
    
    
    function getFieldName($fieldName)
    {
        return $this->getName().ucfirst($fieldName);
    }

    function getFieldId($fieldName)
    {
        return 'fid-' . $this->getFieldName($fieldName);
    }

    /**
     * Render the widget.
     *
     */
    function show()
    {
        $t = $this->getTemplate();

        $this->showDefault($t);
        if ($this->width > 0 && !isset($this->styleList['width'])) {
            $this->addStyle('width', $this->width . 'px');
        }
        if ($this->height > 0 && !isset($this->styleList['height'])) {
            $this->addStyle('height', $this->height . 'px');
        }
        foreach ($this->attrList as $attr => $js) {
            $t->setAttr('canvas', $attr, $js);
        }
        $styleStr = '';
        foreach ($this->styleList as $style => $val) {
            $styleStr .= $style . ': ' . $val . '; ';
        }
        if ($styleStr) {
            $t->setAttr('canvas', 'style', $styleStr);
        }

        $t->setAttr('canvas', 'id', $this->getId());

        $scheme = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $scheme = 'https';
        }

        // Google Maps Start
        $t->appendJsUrl(Tk_Type_Url::create('http://maps.google.com/maps/api/js?sensor=false')->setScheme($scheme));
        
        $tabPane = $this->getForm()->getId().'-tabPane';
        
        $init = <<<JS
var map = null;
var marker = null;
var geocoder = new google.maps.Geocoder();

function geocodePosition(pos)
{
    geocoder.geocode({ latLng: pos },
      function(responses) {
          if (responses && responses.length > 0) {
            //updateMarkerAddress(responses[0].formatted_address);
          } else {
            //updateMarkerAddress('Cannot determine address at this location.');
          }
      }
    );
}
function codeAddress(address) {
  //var address = document.getElementById("address").value;
  geocoder.geocode( { 'address': address}, function(results, status) {
//     vd(results);
//     vd(address);
    if (status == google.maps.GeocoderStatus.OK) {

      map.setCenter(results[0].geometry.location);

    } else {
      alert("Geocode was not successful for the following reason: " + status);
    }
  });
}


function updateElements(latlng, zoom)
{
    var eLat = document.getElementById('{$this->getFieldId('lat')}');
    var eLng = document.getElementById('{$this->getFieldId('lng')}');
    var eZoom = document.getElementById('{$this->getFieldId('zoom')}');
    if (eLat && latlng)  eLat.value = latlng.lat();
    if (eLng && latlng)  eLng.value = latlng.lng();
    if (eZoom && zoom) eZoom.value = zoom;
}
function getCenter()
{
    var eLat = document.getElementById('{$this->getFieldId('lat')}');
    var eLng = document.getElementById('{$this->getFieldId('lng')}');
    if (eLat && eLat.value && eLng && eLng.value ) {
        return new google.maps.LatLng(eLat.value, eLng.value);
    }
    return new google.maps.LatLng(-16.92540, 145.77462);    // Default to Cairns
}
function getZoom()
{
    var eZoom = document.getElementById('{$this->getFieldId('zoom')}');
    if (eZoom != null && eZoom.value != '') {
        if (parseInt(eZoom.value) > 16) return 16;
        if (parseInt(eZoom.value) < 1)  return 1;
        return parseInt(eZoom.value);
    }
    return 12;
}




function initialize() {
  var latLng = getCenter();
  map = new google.maps.Map(document.getElementById('{$this->getId()}'), {
    zoom: getZoom(),
    center: latLng,
    mapTypeId: google.maps.MapTypeId.HYBRID  // ROADMAP, SATELLITE, TERRAIN, HYBRID
  });

  var dat = geocodePosition(latLng);
  console.log(dat);
  marker = new google.maps.Marker({
    position: latLng,
    title: 'A title here',
    map: map,
    draggable: true
  });

  // Update current position info.
  //updateMarkerPosition(latLng);
  //geocodePosition(latLng);

  // Add dragging event listeners.
  google.maps.event.addListener(marker, 'drag', function() {
    updateElements(marker.getPosition(), map.getZoom());
  });
  google.maps.event.addListener(marker, 'dragend', function() {
    updateElements(marker.getPosition(), map.getZoom());
  });
  google.maps.event.addListener(map, 'click', function(e) {
    updateElements(e.latLng, map.getZoom());
    marker.setPosition(e.latLng);
    //map.setCenter(e.latLng);
  });

  google.maps.event.addListener(map, 'zoom_changed', function() {
    updateElements(null, map.getZoom());
  });
}
// Onload handler to fire off the app.

$(document).ready(function() {
    //google.maps.event.addDomListener(window, 'load', initialize);
   initialize();
  $('#$tabPane').bind('tabsshow', function () { google.maps.event.trigger(map, 'resize'); });
});


JS;
        $t->appendHeadElement('script', array('type' => 'text/javascript'), $init);

    }

    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xmlStr = sprintf('<?xml version="1.0"?>
<div class="field" var="block">
  <p class="error" var="error" choice="error"></p>
  <label for="fid-code" var="label"></label>
  <a href="javascript:;" class="fHelp 16-icon-default" title="" var="help" choice="help"><span class="16-icon">?</span></a>

  <div class="fMapSearch">
    <input type="text" name="search" id="fid-search" var="search" style="width: 150px;" />
    <input type="button" onclick="codeAddress($(this).prev().val());" value="Locate" class="tk-ui-btn tk-ui-btn-white tk-ui-btn-small" />
  </div>
  <div style="margin: 10px;margin-left: 12em;"><div class="GMapCanvas" var="canvas"></div></div>
  <!-- div class="fMapFields" style="margin-left: 12em;">
    Map Lat: <input type="text" name="mapLat" id="fid-mapLat" var="mapLat" style="width: 120px;"/>
    Map Long: <input type="text" name="mapLng" id="fid-mapLng" var="mapLng" style="width: 120px;" />
    Map Zoom: <input type="text" name="mapZoom" id="fid-mapZoom" var="mapZoom" style="width: 50px;" />
  </div -->
  <small var="notes" choice="notes"></small>

</div>');
        $template = Dom_Template::load($xmlStr);
        return $template;
    }


}
