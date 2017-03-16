(function() {

  var app = angular.module("yw-editor-app", ['ngFileSaver', 'ngSanitize', 'yw.divider', 'ngAnimate', 'ui.bootstrap']);

  var MainController = function($scope, $http, $timeout, FileSaver, Blob, $window) {

    var editor = ace.edit("editor");
    editor.$blockScrolling = Infinity;

    var viewer = ace.edit("text-viewer");
    viewer.$blockScrolling = Infinity;

    var graph = {}; 
    var workflow_image = {}
    var svg_native_width = 1;
    var svg_native_height = 1;   

    $scope.languageChange = function() {
      editor.session.setMode( "ace/mode/" + $scope.language );
      $scope.getGraph();
      editor.focus();
    }

    $scope.keybindingeChange = function() {
      if ($scope.keybinding === 'ace') {
        editor.setKeyboardHandler(null);
      } else {
        editor.setKeyboardHandler("ace/keyboard/" + $scope.keybinding);
      }
      editor.focus();
    }

    $scope.fontsizeChange = function() {
      editor.setFontSize(parseInt($scope.fontsize));
      viewer.setFontSize(parseInt($scope.fontsize));
      editor.focus();
    }
    
    $scope.downloadGraphImage = function() {
        var file = new Blob([graph.svg], {type: "image/svg+xml"});
        FileSaver.saveAs(file, 'abstract-workflow.svg');
    }
    
    $scope.downloadWorkflowImage = function() {
        var file = new Blob([workflow_image.svg], {type: "image/svg+xml"});
        FileSaver.saveAs(file, 'draft-workflow.svg');
    }
    
    $scope.downloadWorkflow = function() 
    {        
        window.location = $window.Routing.generate('script-converter-draft-workflow-download',{},'true');
    }
    
    $scope.saveScript = function() {
        $http.post(
          $window.Routing.generate('script-converter-save',{},'true'),
          {
                language: $scope.language,
                code: editor.getValue(),
          }).then(function (data)
          {
              $window.toastr.success('Script saved sucessfully!', 'Notification');
          })
        ;
    }
    
    $scope.downloadScript = function() {
        var file = new Blob([editor.getValue()], {type: "text/plain;charset=utf-8"});
        var extension = "sh";
        if ($scope.language == "python")
        {
            extension = "py";
        }
        else if ($scope.language == "sh")
        {
            extension = "sh";
        }
        else if ($scope.language == "r")
        {
            extension = "R";
        }
        FileSaver.saveAs(file, 'script.'+extension);
    }

    $scope.themeChange = function() {

      var aceTheme;
      
      if ($scope.theme == "light") {
        aceTheme = "ace/theme/xcode";
        $scope.background = "#fcfcfc";
        graphViewer.setAttribute('style', "background: white;");      
      } else {
        aceTheme = "ace/theme/tomorrow_night";
        $scope.background = "#b0b0b0";
        graphViewer.setAttribute('style', "background: #b0b0b0;");      
      }
      
      editor.setTheme(aceTheme);
      viewer.setTheme(aceTheme);

      updateSvg();
    }

    $scope.viewerModeChange = function() {
      updateViewer();
      viewer.navigateTo(0,0);
    }

    $scope.showProcessNodesChange = function() {
      if ($scope.showProcessNodes) {
          if ($scope.showDataNodes) {
            $scope.graphView="combined";
          } else {
            $scope.graphView="process";
          }
      } else {
          $scope.showDataNodes=true;
          $scope.graphView="data";
      }
      $scope.getGraph();
    }

    $scope.showDataNodesChange = function() {
      if ($scope.showDataNodes) {
          if ($scope.showProcessNodes) {
            $scope.graphView="combined";
          } else {
            $scope.graphView="data";
          }
      } else {
          $scope.showProcessNodes=true;
          $scope.graphView="process";
      }
      $scope.getGraph();
    }

    $scope.getGraph = function() {
      
        $http.post(
          $window.Routing.generate('yw-graph-service'),
          {
              language: $scope.language,
              code: editor.getValue(),
              properties: "graph.view = " + $scope.graphView + "\n" +
                          "graph.layout = " + $scope.graphLayout + "\n" +
                          "graph.params = " + $scope.graphParams + "\n" +
                          "graph.portlayout = " + $scope.graphPorts + "\n" +
                          "graph.datalabel = " + $scope.dataLabel + "\n" +
                          "graph.programlabel = " + $scope.programLabel + "\n" +
                          "graph.edgelabels = " + $scope.edgeLabels + "\n" +
                          "graph.workflowbox = " + $scope.graphWorkflowBox + "\n" +
                          "graph.titleposition = " + $scope.graphTitlePosition + "\n" +
                          "graph.dotcomments = on\n"
          })
          .then(onGraphComplete);
    }

    var onGraphComplete = function(response) {
      graph = response.data;
      $window.toastr.success('Graph representation updated!', 'Notification');
      updateViewer();
    } 

    $scope.onZoomSelect = function() {
      editor.focus();
    }
    
    var updateViewer = function() {
      
      var content = null;
      
      switch($scope.viewerMode) {                
          
        case "graph":
          if (graph.svg) {
            $scope.showGraphViewer = true;
            updateSvg();
          } else {
            $scope.showGraphViewer = false;
            if (graph.error) {
              viewer.setValue(graph.error);
            } else {
              viewer.setValue("Graph service error");
            }
          }
          break;
          
        case "workflow":
          $scope.showGraphViewer = true;
          $http.get(
            $window.Routing.generate('script-converter-draft-workflow-image',{},'true')
          )
          .then(function(response) {
            workflow_image = response.data;
            updateViewer(); 
            updateSvg();
          });                  
          break;
      }
      
      if (content === null) {
        content = graph.error;
      }
      
      viewer.clearSelection();
    };
    
    $scope.onParentResize = function() {
      onGraphViewerResize();
      editor.resize();
      viewer.resize();
    }

    var updateSvg = function() {

      if (graph.svg == null) return;
      
      switch($scope.viewerMode) {                
          
        case "graph":

            var svgElementStart = graph.svg.search("<svg");
            var svgElement = graph.svg.substring(svgElementStart);
            break;
        case "workflow":
            var svgElementStart = workflow_image.svg.search("<svg");
            var svgElement = workflow_image.svg.substring(svgElementStart);
            break;
      }
      graphViewer.innerHTML = svgElement;
      svg = graphViewer.getElementsByTagName("svg")[0];      
      svg.setAttribute("preserveAspectRatio", "xMinYMin meet");
      svg_native_width = parseInt(svg.getAttribute("width").slice(0, -2));
      svg_native_height = parseInt(svg.getAttribute("height").slice(0, -2));

      var background = svg.getElementsByTagName("polygon")[0];
      if ($scope.theme == "light") {
        background.setAttribute("fill", "white");
      } else {
        background.setAttribute("fill", "#b0b0b0");
      }

      if ($scope.viewerZoom !== "fit") {

        var zoom = parseInt($scope.viewerZoom);
        svg.setAttribute("width", svg_native_width * zoom / 100);
        svg.setAttribute("height", svg_native_height * zoom / 100);

      } else {

        var script_div = document.getElementById("script");
        var viewer_container_div = document.getElementById("viewer");

        var div_width = viewer_container_div.getClientRects()[0].width - 40;
        if (div_width < 1) {
          div_width = 1;
        }

        var div_height = viewer_container_div.getClientRects()[0].height - 40;
        if (div_height < 1) {
          div_height = 1;
        }

        var fit_width_zoom = div_width / svg_native_width;
        var fit_height_zoom = div_height / svg_native_height;
        
        if (fit_height_zoom > fit_width_zoom) {
          svg.setAttribute("width", div_width);
          svg.setAttribute("height", svg_native_height * fit_width_zoom);
        } else {
          svg.setAttribute("height", div_height);
          svg.setAttribute("width", svg_native_width * fit_height_zoom);
        }
      }
    }

    $scope.onScriptSelect = function() {
      $scope.loadSample($scope.sampleToLoad);
    }

    var onSampleLoaded = function(response) {
      editor.setValue(response.data);
      editor.navigateTo(0,0);
      $scope.getGraph();
    }

    $scope.loadSample = function(script) {
        $http.get(script)
          .then(onSampleLoaded);
    }

    function onGraphViewerResize() {
      setTimeout(function () {
        $scope.$apply(function () {
        updateSvg(); 
        });
      }, 100);
    }

    var onLoadInitialScript = function() {
       $scope.loadSample($scope.sampleToLoad);
    }

    window.addEventListener("resize", onGraphViewerResize);

    $scope.theme = "light";
    $scope.keybinding = "ace";
    $scope.language = $window.language;
    editor.session.setMode( "ace/mode/" + $scope.language );

    $scope.fontsize="12";
    $scope.fontsizeChange();

    $scope.viewerMode = "graph";
    $scope.showGraphViewer = false;
    $scope.viewerZoom="fit";
    $scope.sampleToLoad=$window.sample;
    $scope.graphSvg = '';

    $scope.showProcessNodes = true;
    $scope.showDataNodes = true;
    $scope.graphView = 'combined';
    $scope.graphLayout = 'tb';
    $scope.graphParams = 'reduce';
    $scope.graphPorts = 'relax';
    $scope.dataLabel = 'both';
    $scope.programLabel = 'both';
    $scope.edgeLabels = 'hide';
    $scope.graphWorkflowBox = 'show';
    $scope.graphTitlePosition = 'top';

    viewer.setReadOnly(true);
    viewer.setHighlightActiveLine(false);
    viewer.setShowPrintMargin(false);
    viewer.setHighlightGutterLine(false);
    viewer.renderer.setShowGutter(false);
    viewer.session.setMode( "ace/mode/java" );
    editor.setShowPrintMargin(false);

    editor.setKeyboardHandler(null);

    var graphViewer = document.getElementById("graph-viewer");

    $timeout(onLoadInitialScript, 1000);
  };

  app.controller("MainController", ["$scope", "$http", "$timeout", 'FileSaver', 'Blob', '$window', MainController]);

}());
