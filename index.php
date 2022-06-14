<?php
  
if( !class_exists("ServerStatusException") ){
    class ServerStatusException extends Exception { }    
}

if( !class_exists("ServerStatus") ){

  class ServerStatus {

    function __construct() {}

    public function init(){
      try {
        if(!empty($_REQUEST['ajax'])) {
          $content = $this->doAjax();
          echo $content;
        }
        else {
          $content = '';
          $content .= $this->uptimeContent();
          $content .= $this->diskSpaceContent();
          $content .= $this->cpuTempContent();
          $content .= $this->topContent();
          $this->output($content);
        }
      } catch(ServerStatusException $e){
        die("Error: {$e->getMessage()}\n");
      } 
    }

    public function uptimeContent() {
      $content = '';
      //uptime
      $uptime = shell_exec('uptime');
      if(!empty($uptime)) {
        $content =  '<h2>Uptime</h2>';
        $content .= '<pre id="uptime-content">'.$uptime.'</pre>';
      }
      return $content;
    }

    public function diskSpaceContent() {
      $content = '';
      //cpu temp
      $disc_space = shell_exec('df -h -T');
      if(!empty($disc_space)) {
        $content =  '<h2>Disk space</h2>';
        $content .= '<pre id="disk-space-content">'.$disc_space.'</pre>';
      }
      return $content;
    }

    public function cpuTempContent() {
      $content = '';
      //disc space
      $cpu_temp = shell_exec('sysctl dev.cpu | grep temperature');
      if(!empty($cpu_temp)) {
        $content =  '<h2>CPU Temp</h2>';
        $content .= '<pre id="cpu-temp-content">'.$cpu_temp.'</pre>';
      }
      return $content;
    }

    public function topContent($n = 20) {
      $content = '';
      //top
      $top = shell_exec('top -n '.$n);
      if(!empty($top)) {
        $content =  '<h2>Processes</h2>';
        $content .= '<pre id="top-content">'.$top.'</pre>';
      }
      return $content;
    }

    function doAjax() {
      $content = '';
      if(!empty($_REQUEST['ajax'])) {
        switch ($_REQUEST['param']) {
          case 'cpu_temp':
            $content = shell_exec('sysctl dev.cpu | grep temperature');
            break;

          case 'top':
            $content = shell_exec('top -n 20');
            break;
          
          default:
            // code...
            break;
        }
      }
      return $content;
    }

    public function output($content) {
      $title = 'Server Status';
      $styles = '<style>body {background: #f5f5f5;color: #3c434a;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;font-size: 13px;line-height: 1.4;}</style>';
      $script = '<script type="text/javascript">
      (function() {
        function callAjax(url, elemId) {
            var xmlhttp;
            // compatible with IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
                    updateAjaxContent(xmlhttp.responseText, elemId);
                }
            }
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }
        function updateAjaxContent(responseText, elemId) {
          if (responseText) {
            var container = document.getElementById(elemId);
            container.innerHTML = responseText;
          }
        }
        var element = document.getElementById("cpu-temp-content");
        if(typeof(element) != "undefined" && element != null){
          //callAjax(window.location.href + "?ajax=1&param=top", "top-content");
          var intervalTop = setInterval(callAjax, 9000, window.location.href + "?ajax=1&param=cpu_temp", "cpu-temp-content");
        }
        var element = document.getElementById("top-content");
        if(typeof(element) != "undefined" && element != null){
          //callAjax(window.location.href + "?ajax=1&param=top", "top-content");
          var intervalTop = setInterval(callAjax, 11000, window.location.href + "?ajax=1&param=top", "top-content");
        }
      })();
      </script>';
      echo sprintf('<!DOCTYPE html><html><head><title>%s</title>%s</head><body><div id="container">%s</div>%s</body></html>', $title, $styles, $content, $script);
    }

  }

  (new ServerStatus())->init();
}

