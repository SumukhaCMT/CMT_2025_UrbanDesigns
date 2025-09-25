/*------------------------------------------------------------------------------*/
/*  Home_Page Slider
/*------------------------------------------------------------------------------*/

var revapi2,

  tpj;
  
  jQuery(function() {
    tpj = jQuery;
    if(tpj("#rev_slider_1_1").revolution == undefined){
      revslider_showDoubleJqueryError("#rev_slider_1_1");
    }else{
        revapi2 = tpj("#rev_slider_1_1").show().revolution({
        sliderType:"standard",
        sliderLayout:"fullwidth",
        visibilityLevels:"1240,1240,778,480",
        gridwidth:"1240,1240,778,480",
        gridheight:"861,861,450,350",
        spinner:"spinner0",
        perspective:600,
        perspectiveType:"local",
        editorheight:"861,861,450,350",
        responsiveLevels:"1240,1240,778,480",
        disableProgressBar:true,
          navigation: {
            onHoverStop:false,
            arrows: {
              enable:true,
              style:"uranus",
              hide_onmobile:true,
              hide_under:778,
              left: {
                h_offset:30
              },
              right: {
                h_offset:30
              }
            }
          },
        fallbacks: {
          allowHTML5AutoPlayOnAndroid:true
        },
      });
    }
    
  });



  /* (homepage -2 )*/

  jQuery(function() {
    tpj = jQuery;

    if(tpj("#rev_slider_2_1").revolution == undefined){
        revslider_showDoubleJqueryError("#rev_slider_2_1");

      }else{

        revapi2 = tpj("#rev_slider_2_1").show().revolution({
        sliderType:"standard",
        visibilityLevels:"1240,1240,778,480",
        gridwidth:"1170,1170,778,480",
        gridheight:"340,340,340,800",
        perspective:600,
        perspectiveType:"global",
        editorheight:"340,768,340,800",
        responsiveLevels:"1240,1240,778,480",
        progressBar:{disableProgressBar:true},
        navigation: {
          onHoverStop:false
        },
        viewPort: {
          global:false,
          globalDist:"-200px",
          enable:false
        },
        fallbacks: {
          allowHTML5AutoPlayOnAndroid:true
        },
      });
    }
  });



  /* (homepage -3 )*/
  jQuery(function() {
    tpj = jQuery;

    if(tpj("#rev_slider_3_1").revolution == undefined){
        revslider_showDoubleJqueryError("#rev_slider_3_1");
        
      }else{

        revapi2 = tpj("#rev_slider_3_1").show().revolution({
        sliderType:"standard",
        visibilityLevels:"1240,1240,778,480",
        gridwidth:"1240,1240,778,480",
        gridheight:"750,750,450,350",
        spinner:"spinner0",
        perspective:600,
        perspectiveType:"local",
        editorheight:"750,750,450,350",
        responsiveLevels:"1240,1240,778,480",
        disableProgressBar:true,
          navigation: {
            onHoverStop:false,
            arrows: {
              enable:true,
              style:"uranus",
              hide_onmobile:true,
              hide_under:778,
              left: {
                h_offset:30
              },
              right: {
                h_offset:30
              }
            }
          },
        fallbacks: {
          allowHTML5AutoPlayOnAndroid:true
        },
      });
    }
  });


  /* (homepage -3 )*/
  jQuery(function() {
    tpj = jQuery;

    if(tpj("#rev_slider_4_1").revolution == undefined){
        revslider_showDoubleJqueryError("#rev_slider_4_1");
        
      }else{

        revapi2 = tpj("#rev_slider_4_1").show().revolution({
        sliderType:"standard",
        visibilityLevels:"1240,1240,778,480",
        gridwidth:"1240,1240,778,480",
        gridheight:"861,861,450,350",
        spinner:"spinner0",
        perspective:600,
        perspectiveType:"local",
        editorheight:"861,861,450,350",
        responsiveLevels:"1240,1240,778,480",
        disableProgressBar:true,
          navigation: {
            onHoverStop:false,
            arrows: {
              enable:true,
              style:"uranus",
              hide_onmobile:true,
              hide_under:778,
              left: {
                h_offset:30
              },
              right: {
                h_offset:30
              }
            }
          },
        fallbacks: {
          allowHTML5AutoPlayOnAndroid:true
        },
      });
    }
  });